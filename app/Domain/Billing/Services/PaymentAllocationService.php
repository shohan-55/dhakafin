<?php

namespace App\Domain\Billing\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PaymentAllocationService
{
    public function allocate(Payment $payment, Invoice $invoice, int $amountMinor): PaymentAllocation
    {
        if ($amountMinor<=0) {
            throw new InvalidArgumentException('Allocation amount must be positive.');
        }

        return DB::transaction(function () use ($payment,$invoice,$amountMinor): PaymentAllocation {
            $payment=Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $invoice=Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($payment->organization_id!==$invoice->organization_id) {
                throw new InvalidArgumentException('Payment and invoice must belong to the same organization.');
            }

            if ($payment->status!=='confirmed') {
                throw new InvalidArgumentException('Only confirmed payments may be allocated.');
            }

            $available=$payment->amount_minor-$payment->allocated_amount_minor;

            if ($amountMinor>$available) {
                throw new InvalidArgumentException('Allocation exceeds unapplied payment amount.');
            }

            if ($amountMinor>$invoice->balance_minor) {
                throw new InvalidArgumentException('Allocation exceeds invoice balance.');
            }

            $allocation=PaymentAllocation::query()
                ->where('payment_id',$payment->id)
                ->where('invoice_id',$invoice->id)
                ->lockForUpdate()
                ->first();

            if ($allocation) {
                $allocation->increment('amount_minor',$amountMinor);
                $allocation->refresh();
            } else {
                $allocation=PaymentAllocation::create([
                    'payment_id'=>$payment->id,
                    'invoice_id'=>$invoice->id,
                    'amount_minor'=>$amountMinor,
                ]);
            }

            $newAllocated=$payment->allocated_amount_minor+$amountMinor;
            $payment->forceFill([
                'allocated_amount_minor'=>$newAllocated,
                'unapplied_amount_minor'=>$payment->amount_minor-$newAllocated,
            ])->save();

            $newPaid=$invoice->paid_minor+$amountMinor;
            $newBalance=$invoice->total_minor-$newPaid;

            $invoice->forceFill([
                'paid_minor'=>$newPaid,
                'balance_minor'=>$newBalance,
                'status'=>$newBalance===0 ? 'paid' : 'partially_paid',
            ])->save();

            return $allocation;
        });
    }
}
