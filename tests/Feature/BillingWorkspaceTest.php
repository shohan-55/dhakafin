<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_invoice_issue_and_allocate_overpayment(): void
    {
        $this->withoutVite();

        [$user,$organization]=$this->billingUser();

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.billing.invoices.store'),[
                'issue_date'=>'2026-10-01',
                'due_date'=>'2026-10-31',
                'rows'=>[
                    [
                        'description'=>'Monthly accounting service',
                        'quantity'=>'1',
                        'unit_amount'=>'1,000.00',
                        'tax_amount'=>'150.00',
                    ],
                ],
            ])
            ->assertRedirect(route('workspace.billing.index'));

        $invoice=Invoice::firstOrFail();

        $this->assertSame($organization->id,$invoice->organization_id);
        $this->assertSame(115000,$invoice->total_minor);
        $this->assertSame(115000,$invoice->balance_minor);
        $this->assertSame('draft',$invoice->status);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.billing.invoices.issue',$invoice))
            ->assertRedirect(route('workspace.billing.index'));

        $this->assertSame('issued',$invoice->fresh()->status);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.billing.payments.store'),[
                'payment_date'=>'2026-10-10',
                'amount'=>'1,200.00',
                'method'=>'bank',
                'reference'=>'BANK-TRX-001',
                'idempotency_key'=>'workspace-pay-001',
                'invoice_id'=>$invoice->id,
            ])
            ->assertRedirect(route('workspace.billing.index'));

        $payment=Payment::firstOrFail();
        $invoice->refresh();

        $this->assertSame('paid',$invoice->status);
        $this->assertSame(115000,$invoice->paid_minor);
        $this->assertSame(0,$invoice->balance_minor);
        $this->assertSame(115000,$payment->allocated_amount_minor);
        $this->assertSame(5000,$payment->unapplied_amount_minor);

        $this->assertDatabaseHas('audit_events',[
            'organization_id'=>$organization->id,
            'action'=>'billing.invoice.created',
        ]);
        $this->assertDatabaseHas('audit_events',[
            'organization_id'=>$organization->id,
            'action'=>'billing.invoice.issued',
        ]);
        $this->assertDatabaseHas('audit_events',[
            'organization_id'=>$organization->id,
            'action'=>'billing.payment.recorded',
        ]);
    }

    public function test_user_cannot_issue_foreign_invoice_even_with_current_org_permission(): void
    {
        $this->withoutVite();

        [$user,$organization]=$this->billingUser();
        $foreign=Organization::create(['name'=>'Foreign Billing','slug'=>'foreign-billing']);

        $foreignInvoice=Invoice::create([
            'organization_id'=>$foreign->id,
            'number'=>'INV-FOREIGN-001',
            'issue_date'=>'2026-10-01',
            'status'=>'draft',
            'currency'=>'BDT',
            'subtotal_minor'=>10000,
            'tax_minor'=>0,
            'total_minor'=>10000,
            'paid_minor'=>0,
            'balance_minor'=>10000,
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.billing.invoices.issue',$foreignInvoice))
            ->assertNotFound();
    }

    private function billingUser(): array
    {
        $user=User::factory()->create();
        $organization=Organization::create(['name'=>'Billing Workspace Co','slug'=>'billing-workspace-co']);
        $user->organizations()->attach($organization,['status'=>'active','role'=>'member']);

        $view=Permission::firstOrCreate(['slug'=>'billing.view'],['name'=>'Billing View']);
        $manage=Permission::firstOrCreate(['slug'=>'billing.manage'],['name'=>'Billing Manage']);
        $role=Role::create(['name'=>'Billing Manager','slug'=>'billing-manager-test','scope'=>'organization']);
        $role->permissions()->attach([$view->id,$manage->id]);
        $user->roles()->attach($role,['organization_id'=>$organization->id]);

        return [$user,$organization];
    }
}
