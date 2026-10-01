<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WithholdingTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithholdingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_withholding_workspace_is_tenant_scoped(): void
    {
        $this->withoutVite();

        [$user, $organization] = $this->taxUser();
        $foreign = Organization::create(['name'=>'Foreign Co','slug'=>'foreign-tax-co']);

        WithholdingTransaction::create([
            'organization_id'=>$organization->id,
            'kind'=>'tds',
            'transaction_date'=>'2026-10-01',
            'counterparty_name'=>'Own Vendor',
            'base_amount_minor'=>100000,
            'rate_ppm'=>100000,
            'withheld_amount_minor'=>10000,
            'status'=>'draft',
        ]);

        WithholdingTransaction::create([
            'organization_id'=>$foreign->id,
            'kind'=>'tds',
            'transaction_date'=>'2026-10-01',
            'counterparty_name'=>'Foreign Vendor',
            'base_amount_minor'=>100000,
            'rate_ppm'=>100000,
            'withheld_amount_minor'=>10000,
            'status'=>'draft',
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->get(route('workspace.withholding.index'))
            ->assertOk()
            ->assertSee('Own Vendor')
            ->assertDontSee('Foreign Vendor');
    }

    public function test_authorized_user_can_store_exact_withholding_values_and_audit_event(): void
    {
        [$user, $organization] = $this->taxUser();

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.withholding.store'), [
                'kind'=>'vds',
                'transaction_date'=>'2026-10-01',
                'counterparty_name'=>'Supplier Ltd',
                'amount'=>'100,000.00',
                'rate'=>'10',
            ])
            ->assertRedirect(route('workspace.withholding.index'));

        $this->assertDatabaseHas('withholding_transactions', [
            'organization_id'=>$organization->id,
            'kind'=>'vds',
            'base_amount_minor'=>10000000,
            'rate_ppm'=>100000,
            'withheld_amount_minor'=>1000000,
        ]);

        $this->assertDatabaseHas('audit_events', [
            'organization_id'=>$organization->id,
            'actor_user_id'=>$user->id,
            'action'=>'tax.withholding.created',
        ]);
    }

    private function taxUser(): array
    {
        $user = User::factory()->create();
        $organization = Organization::create(['name'=>'Tax Co','slug'=>'tax-co']);
        $user->organizations()->attach($organization, ['status'=>'active','role'=>'member']);

        $view = Permission::create(['name'=>'Tax View','slug'=>'tax.view']);
        $manage = Permission::create(['name'=>'Tax Manage','slug'=>'tax.manage']);
        $role = Role::create(['name'=>'Tax Manager','slug'=>'tax-manager-workspace','scope'=>'organization']);
        $role->permissions()->attach([$view->id,$manage->id]);
        $user->roles()->attach($role, ['organization_id'=>$organization->id]);

        return [$user,$organization];
    }
}
