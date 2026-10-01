<?php

namespace Tests\Feature;

use App\Domain\Billing\Services\InvoiceService;
use App\Domain\Billing\Services\PaymentAllocationService;
use App\Domain\Billing\Services\PaymentService;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BillingCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_and_partial_payment_allocation_preserve_integer_money(): void
    {
        $organization=Organization::create(['name'=>'Billing Co','slug'=>'billing-co']);

        $invoice=app(InvoiceService::class)->createDraft(
            $organization,
            '2026-10-01',
            '2026-10-31',
            [
                [
                    'description'=>'Monthly accounting',
                    'quantity_milli'=>1000,
                    'unit_amount_minor'=>100000,
                    'tax_minor'=>15000,
                ],
                [
                    'description'=>'Advisory hours',
                    'quantity_milli'=>1500,
                    'unit_amount_minor'=>20000,
                    'tax_minor'=>4500,
                ],
            ]
        );

        $this->assertSame(130000,$invoice->subtotal_minor);
        $this->assertSame(19500,$invoice->tax_minor);
        $this->assertSame(149500,$invoice->total_minor);
        $this->assertSame(149500,$invoice->balance_minor);

        app(InvoiceService::class)->issue($invoice);

        $payment=app(PaymentService::class)->record(
            $organization,
            '2026-10-10',
            200000,
            'bank',
            'BANK-001',
            'pay-idem-001'
        );

        app(PaymentAllocationService::class)->allocate($payment,$invoice,100000);

        $invoice->refresh();
        $payment->refresh();

        $this->assertSame(100000,$invoice->paid_minor);
        $this->assertSame(49500,$invoice->balance_minor);
        $this->assertSame('partially_paid',$invoice->status);
        $this->assertSame(100000,$payment->allocated_amount_minor);
        $this->assertSame(100000,$payment->unapplied_amount_minor);

        app(PaymentAllocationService::class)->allocate($payment,$invoice,49500);

        $this->assertSame('paid',$invoice->refresh()->status);
        $this->assertSame(0,$invoice->balance_minor);
        $this->assertSame(50500,$payment->refresh()->unapplied_amount_minor);
    }

    public function test_payment_idempotency_replays_same_payload_and_rejects_conflict(): void
    {
        $organization=Organization::create(['name'=>'Idempotent Co','slug'=>'idem-co']);
        $service=app(PaymentService::class);

        $first=$service->record($organization,'2026-10-01',50000,'cash','R-1','idem-1');
        $second=$service->record($organization,'2026-10-01',50000,'cash','R-1','idem-1');

        $this->assertSame($first->id,$second->id);

        $this->expectException(InvalidArgumentException::class);
        $service->record($organization,'2026-10-01',60000,'cash','R-1','idem-1');
    }
}
