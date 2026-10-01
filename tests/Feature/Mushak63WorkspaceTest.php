<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\MushakForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Mushak63WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_tenant_scoped_mushak_draft(): void
    {
        $user = User::factory()->create();
        $organization = Organization::create(['name'=>'VAT Co','slug'=>'vat-co']);
        $user->organizations()->attach($organization, ['status'=>'active','role'=>'member']);

        $view = Permission::create(['name'=>'VAT View','slug'=>'vat.view']);
        $manage = Permission::create(['name'=>'VAT Manage','slug'=>'vat.manage']);
        $role = Role::create(['name'=>'VAT Manager','slug'=>'vat-manager-test','scope'=>'organization']);
        $role->permissions()->attach([$view->id,$manage->id]);
        $user->roles()->attach($role, ['organization_id'=>$organization->id]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.mushak63.store'), [
                'serial_no'=>'M63-001',
                'issue_date'=>'2026-10-01',
                'buyer_name'=>'Buyer Ltd',
                'rows'=>[
                    ['description'=>'Service A','quantity'=>'2','value'=>'10,000.00','vat'=>'1,500.00'],
                    ['description'=>'Service B','quantity'=>'1.5','value'=>'5,000.00','vat'=>'750.00'],
                ],
            ])
            ->assertRedirect(route('workspace.mushak63.index'));

        $form = MushakForm::firstOrFail();

        $this->assertSame($organization->id, $form->organization_id);
        $this->assertSame(1500000, $form->total_value_minor);
        $this->assertSame(225000, $form->total_vat_minor);
        $this->assertCount(2, $form->lines);

        $this->assertDatabaseHas('audit_events', [
            'organization_id'=>$organization->id,
            'actor_user_id'=>$user->id,
            'action'=>'vat.mushak63.draft_created',
        ]);
    }
}
