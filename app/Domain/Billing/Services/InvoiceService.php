<?php

namespace App\Domain\Billing\Services;

use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class InvoiceService
{
    public function createDraft(
        Organization $organization,
        string $issueDate,
        ?string $dueDate,
        array $lines,
        ?string $notes=null,
    ): Invoice {
        if ($lines===[]) {
            throw new InvalidArgumentException('Invoice requires at least one line.');
        }

        return DB::transaction(function () use ($organization,$issueDate,$dueDate,$lines,$notes): Invoice {
            do {
                $number='INV-'.now('Asia/Dhaka')->format('ymd').'-'.Str::upper(Str::random(6));
            } while (Invoice::where('organization_id',$organization->id)->where('number',$number)->exists());

            $normalized=[];
            $subtotal=0;
            $tax=0;

            foreach ($lines as $line) {
                $description=trim((string)($line['description'] ?? ''));
                $quantity=(int)($line['quantity_milli'] ?? 1000);
                $unit=(int)($line['unit_amount_minor'] ?? -1);
                $lineTax=(int)($line['tax_minor'] ?? 0);

                if ($description==='' || $quantity<=0 || $unit<0 || $lineTax<0) {
                    throw new InvalidArgumentException('Invalid invoice line.');
                }

                $lineTotal=intdiv(($quantity*$unit)+500,1000);

                $normalized[]=[
                    'description'=>$description,
                    'quantity_milli'=>$quantity,
                    'unit_amount_minor'=>$unit,
                    'line_total_minor'=>$lineTotal,
                    'tax_minor'=>$lineTax,
                ];

                $subtotal+=$lineTotal;
                $tax+=$lineTax;
            }

            $total=$subtotal+$tax;

            $invoice=Invoice::create([
                'organization_id'=>$organization->id,
                'number'=>$number,
                'issue_date'=>$issueDate,
                'due_date'=>$dueDate,
                'status'=>'draft',
                'currency'=>'BDT',
                'subtotal_minor'=>$subtotal,
                'tax_minor'=>$tax,
                'total_minor'=>$total,
                'paid_minor'=>0,
                'balance_minor'=>$total,
                'notes'=>$notes,
            ]);

            $invoice->lines()->createMany($normalized);

            return $invoice->load('lines');
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status!=='draft') {
            throw new InvalidArgumentException('Only draft invoices can be issued.');
        }

        $invoice->forceFill(['status'=>'issued'])->save();

        return $invoice->refresh();
    }
}
