<?php

namespace App\Http\Controllers\Workspace;

use App\Domain\Billing\Services\InvoiceService;
use App\Domain\Billing\Services\PaymentAllocationService;
use App\Domain\Billing\Services\PaymentService;
use App\Domain\TaxVat\Services\BdtMoney;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class BillingController extends Controller
{
    public function index(TenantContext $tenants): View
    {
        $organization=$tenants->current();

        return view('workspace.billing.index',[
            'organization'=>$organization,
            'invoices'=>Invoice::query()
                ->where('organization_id',$organization->id)
                ->orderByDesc('issue_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
            'payments'=>\App\Models\Payment::query()
                ->where('organization_id',$organization->id)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }

    public function createInvoice(TenantContext $tenants): View
    {
        return view('workspace.billing.invoice-create',['organization'=>$tenants->current()]);
    }

    public function storeInvoice(
        Request $request,
        TenantContext $tenants,
        BdtMoney $money,
        InvoiceService $invoices,
        AuditLogger $audit,
    ): RedirectResponse {
        $validated=$request->validate([
            'issue_date'=>['required','date'],
            'due_date'=>['nullable','date','after_or_equal:issue_date'],
            'notes'=>['nullable','string','max:4000'],
            'rows'=>['required','array','min:1','max:20'],
            'rows.*.description'=>['nullable','string','max:500'],
            'rows.*.quantity'=>['nullable','string','max:30'],
            'rows.*.unit_amount'=>['nullable','string','max:30'],
            'rows.*.tax_amount'=>['nullable','string','max:30'],
        ]);

        try {
            $lines=[];
            foreach($validated['rows'] as $row){
                $description=trim((string)($row['description'] ?? ''));
                $quantity=trim((string)($row['quantity'] ?? ''));
                $unit=trim((string)($row['unit_amount'] ?? ''));
                $tax=trim((string)($row['tax_amount'] ?? ''));

                if($description==='' && $quantity==='' && $unit==='' && $tax===''){
                    continue;
                }

                $lines[]=[
                    'description'=>$description,
                    'quantity_milli'=>$this->parseQuantityMilli($quantity),
                    'unit_amount_minor'=>$money->parseToMinor($unit),
                    'tax_minor'=>$money->parseToMinor($tax==='' ? '0' : $tax),
                ];
            }

            $invoice=$invoices->createDraft(
                $tenants->current(),
                $validated['issue_date'],
                $validated['due_date'] ?? null,
                $lines,
                $validated['notes'] ?? null,
            );
        } catch(InvalidArgumentException $exception){
            throw ValidationException::withMessages(['rows'=>$exception->getMessage()]);
        }

        $audit->record('billing.invoice.created',$request->user(),$tenants->current(),$invoice,request:$request);

        return redirect()->route('workspace.billing.index')->with('status','Invoice draft created.');
    }

    public function issue(
        Request $request,
        Invoice $invoice,
        TenantContext $tenants,
        InvoiceService $invoices,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($invoice->organization_id===$tenants->id(),404);

        try {
            $invoices->issue($invoice);
        } catch(InvalidArgumentException $exception){
            throw ValidationException::withMessages(['invoice'=>$exception->getMessage()]);
        }

        $audit->record('billing.invoice.issued',$request->user(),$tenants->current(),$invoice,request:$request);

        return redirect()->route('workspace.billing.index');
    }

    public function storePayment(
        Request $request,
        TenantContext $tenants,
        BdtMoney $money,
        PaymentService $payments,
        PaymentAllocationService $allocations,
        AuditLogger $audit,
    ): RedirectResponse {
        $validated=$request->validate([
            'payment_date'=>['required','date'],
            'amount'=>['required','string','max:30'],
            'method'=>['required','in:cash,bank,bkash,nagad,card,other'],
            'reference'=>['nullable','string','max:190'],
            'idempotency_key'=>['required','string','max:100'],
            'invoice_id'=>['nullable','integer'],
            'allocation_amount'=>['nullable','string','max:30'],
        ]);

        try {
            $amountMinor=$money->parseToMinor($validated['amount']);
            $payment=$payments->record(
                $tenants->current(),
                $validated['payment_date'],
                $amountMinor,
                $validated['method'],
                $validated['reference'] ?? null,
                $validated['idempotency_key'],
                $request->user()->id,
            );

            if(!empty($validated['invoice_id'])){
                $invoice=Invoice::query()
                    ->where('organization_id',$tenants->id())
                    ->findOrFail($validated['invoice_id']);

                $allocationMinor=!empty($validated['allocation_amount'])
                    ? $money->parseToMinor($validated['allocation_amount'])
                    : min($payment->unapplied_amount_minor,$invoice->balance_minor);

                if($allocationMinor>0){
                    $allocations->allocate($payment,$invoice,$allocationMinor);
                }
            }
        } catch(InvalidArgumentException $exception){
            throw ValidationException::withMessages(['amount'=>$exception->getMessage()]);
        }

        $audit->record('billing.payment.recorded',$request->user(),$tenants->current(),$payment,request:$request);

        return redirect()->route('workspace.billing.index')->with('status','Payment recorded.');
    }

    private function parseQuantityMilli(string $quantity): int
    {
        if(!preg_match('/^\d+(?:\.\d{1,3})?$/',$quantity)){
            throw new InvalidArgumentException('Quantity must have up to three decimal places.');
        }

        [$whole,$fraction]=array_pad(explode('.',$quantity,2),2,'');
        $value=((int)$whole*1000)+(int)str_pad($fraction,3,'0');

        if($value<=0){
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        return $value;
    }
}
