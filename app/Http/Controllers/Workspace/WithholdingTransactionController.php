<?php

namespace App\Http\Controllers\Workspace;

use App\Domain\TaxVat\Services\BdtMoney;
use App\Domain\TaxVat\Services\WithholdingCalculator;
use App\Http\Controllers\Controller;
use App\Models\WithholdingTransaction;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class WithholdingTransactionController extends Controller
{
    public function index(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        return view('workspace.withholding.index', [
            'organization' => $organization,
            'transactions' => WithholdingTransaction::query()
                ->where('organization_id', $organization->getKey())
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function create(TenantContext $tenants): View
    {
        return view('workspace.withholding.create', [
            'organization' => $tenants->current(),
        ]);
    }

    public function store(
        Request $request,
        TenantContext $tenants,
        BdtMoney $money,
        WithholdingCalculator $calculator,
        AuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'kind' => ['required','in:tds,vds'],
            'transaction_date' => ['required','date'],
            'reference' => ['nullable','string','max:100'],
            'counterparty_name' => ['nullable','string','max:190'],
            'counterparty_tin' => ['nullable','string','max:50'],
            'amount' => ['required','regex:/^\s*[\d,]+(?:\.\d{1,2})?\s*$/'],
            'rate' => ['required','regex:/^\d+(?:\.\d{1,4})?$/'],
            'challan_reference' => ['nullable','string','max:100'],
            'challan_date' => ['nullable','date'],
        ]);

        try {
            $baseMinor = $money->parseToMinor($validated['amount']);
            $calculation = $calculator->calculate($baseMinor, $validated['rate']);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['amount' => $exception->getMessage()]);
        }

        $transaction = WithholdingTransaction::create([
            'organization_id' => $tenants->id(),
            'kind' => $validated['kind'],
            'reference' => $validated['reference'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'counterparty_name' => $validated['counterparty_name'] ?? null,
            'counterparty_tin' => $validated['counterparty_tin'] ?? null,
            'base_amount_minor' => $calculation['base_amount_minor'],
            'rate_ppm' => $calculation['rate_ppm'],
            'withheld_amount_minor' => $calculation['withheld_amount_minor'],
            'challan_reference' => $validated['challan_reference'] ?? null,
            'challan_date' => $validated['challan_date'] ?? null,
            'status' => filled($validated['challan_reference'] ?? null) ? 'paid' : 'draft',
            'created_by' => $request->user()->getKey(),
        ]);

        $audit->record(
            action: 'tax.withholding.created',
            actor: $request->user(),
            organization: $tenants->current(),
            auditable: $transaction,
            metadata: ['kind' => $transaction->kind],
            request: $request,
        );

        return redirect()
            ->route('workspace.withholding.index')
            ->with('status', 'Withholding transaction saved.');
    }
}
