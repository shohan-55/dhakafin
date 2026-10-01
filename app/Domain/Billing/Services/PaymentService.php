<?php

namespace App\Domain\Billing\Services;

use App\Models\Organization;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PaymentService
{
    public function record(
        Organization $organization,
        string $paymentDate,
        int $amountMinor,
        string $method,
        ?string $reference=null,
        ?string $idempotencyKey=null,
        ?int $receivedBy=null,
    ): Payment {
        if ($amountMinor<=0) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }

        return DB::transaction(function () use (
            $organization,$paymentDate,$amountMinor,$method,$reference,$idempotencyKey,$receivedBy
        ): Payment {
            if ($idempotencyKey) {
                $existing=Payment::query()
                    ->where('organization_id',$organization->id)
                    ->where('idempotency_key',$idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            return Payment::create([
                'organization_id'=>$organization->id,
                'idempotency_key'=>$idempotencyKey,
                'payment_date'=>$paymentDate,
                'amount_minor'=>$amountMinor,
                'allocated_amount_minor'=>0,
                'unapplied_amount_minor'=>$amountMinor,
                'method'=>$method,
                'reference'=>$reference,
                'status'=>'confirmed',
                'received_by'=>$receivedBy,
            ]);
        });
    }
}
