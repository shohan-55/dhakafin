<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_is_scoped_to_the_selected_organization(): void
    {
        $user = User::factory()->create();
        $one = Organization::create(['name'=>'One','slug'=>'one']);
        $two = Organization::create(['name'=>'Two','slug'=>'two']);

        $permission = Permission::create(['name'=>'Tax Manage','slug'=>'tax.manage']);
        $role = Role::create(['name'=>'Tax Manager','slug'=>'tax-manager','scope'=>'organization']);
        $role->permissions()->attach($permission);

        $user->roles()->attach($role, ['organization_id'=>$one->id]);

        $this->assertTrue($user->hasPermission('tax.manage', $one));
        $this->assertFalse($user->hasPermission('tax.manage', $two));
    }
}
