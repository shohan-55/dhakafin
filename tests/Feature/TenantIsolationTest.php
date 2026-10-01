<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_activate_an_organization_they_do_not_belong_to(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $owned = Organization::create(['name'=>'Owned Co','slug'=>'owned-co']);
        $foreign = Organization::create(['name'=>'Foreign Co','slug'=>'foreign-co']);

        $user->organizations()->attach($owned, ['status'=>'active','role'=>'member']);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$foreign->id])
            ->get(route('workspace.dashboard'))
            ->assertForbidden();
    }

    public function test_active_member_can_open_their_organization_workspace(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $organization = Organization::create(['name'=>'Client Co','slug'=>'client-co']);
        $user->organizations()->attach($organization, ['status'=>'active','role'=>'member']);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->get(route('workspace.dashboard'))
            ->assertOk()
            ->assertSee('Client Co');
    }
}
