<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_requires_the_organization_permission(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $organization = Organization::create(['name'=>'Secure Co','slug'=>'secure-co']);
        $user->organizations()->attach($organization, ['status'=>'active','role'=>'member']);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->get(route('workspace.audit-log'))
            ->assertForbidden();

        $permission = Permission::create(['name'=>'Audit Log View','slug'=>'audit-log.view']);
        $role = Role::create(['name'=>'Reviewer','slug'=>'reviewer','scope'=>'organization']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role, ['organization_id'=>$organization->id]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->get(route('workspace.audit-log'))
            ->assertOk();
    }
}
