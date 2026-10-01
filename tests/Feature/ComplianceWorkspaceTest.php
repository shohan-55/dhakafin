<?php

namespace Tests\Feature;

use App\Models\ComplianceObligation;
use App\Models\ComplianceRule;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ComplianceWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_monthly_rule_generates_idempotent_organization_obligation(): void
    {
        $organization = Organization::create(['name'=>'Compliance Co','slug'=>'compliance-co']);

        $rule = ComplianceRule::create([
            'code'=>'DEMO-MONTHLY-01',
            'category'=>'vat',
            'title'=>'Verified monthly demo obligation',
            'frequency'=>'monthly',
            'rule_data'=>['day'=>31,'month_offset'=>0],
            'effective_from'=>'2026-01-01',
            'source_url'=>'https://example.test/official-rule',
            'verified_at'=>now(),
            'published_at'=>now(),
            'is_active'=>true,
        ]);

        Artisan::call('dhakafin:compliance-generate', [
            '--month'=>'2026-11',
            '--organization'=>$organization->id,
        ]);

        Artisan::call('dhakafin:compliance-generate', [
            '--month'=>'2026-11',
            '--organization'=>$organization->id,
        ]);

        $this->assertSame(1, ComplianceObligation::count());

        $obligation = ComplianceObligation::firstOrFail();
        $this->assertSame($rule->id, $obligation->compliance_rule_id);
        $this->assertSame('2026-11-30', $obligation->due_date->toDateString());
        $this->assertSame('2026-11', $obligation->period_key);
    }

    public function test_authorized_user_can_complete_only_current_tenant_obligation(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $organization = Organization::create(['name'=>'Own Compliance','slug'=>'own-compliance']);
        $foreign = Organization::create(['name'=>'Other Compliance','slug'=>'other-compliance']);
        $user->organizations()->attach($organization, ['status'=>'active','role'=>'member']);

        $view = Permission::create(['name'=>'Compliance View','slug'=>'compliance.view']);
        $manage = Permission::create(['name'=>'Compliance Manage','slug'=>'compliance.manage']);
        $role = Role::create(['name'=>'Compliance Manager','slug'=>'compliance-manager-test','scope'=>'organization']);
        $role->permissions()->attach([$view->id,$manage->id]);
        $user->roles()->attach($role, ['organization_id'=>$organization->id]);

        $own = ComplianceObligation::create([
            'organization_id'=>$organization->id,
            'type'=>'tax',
            'title'=>'Own deadline',
            'period_key'=>'2026-10-own',
            'due_date'=>'2026-10-31',
            'status'=>'open',
        ]);

        $other = ComplianceObligation::create([
            'organization_id'=>$foreign->id,
            'type'=>'tax',
            'title'=>'Foreign deadline',
            'period_key'=>'2026-10-foreign',
            'due_date'=>'2026-10-31',
            'status'=>'open',
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.compliance.complete',$own), [
                'evidence_reference'=>'CHALLAN-001',
            ])
            ->assertRedirect(route('workspace.compliance.index'));

        $this->assertDatabaseHas('compliance_obligations', [
            'id'=>$own->id,
            'status'=>'completed',
            'evidence_reference'=>'CHALLAN-001',
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.compliance.complete',$other))
            ->assertNotFound();
    }
}
