<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Journal;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_bootstrap_create_and_post_balanced_journal(): void
    {
        $this->withoutVite();

        [$user,$organization]=$this->accountingUser();

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.accounting.bootstrap'))
            ->assertRedirect(route('workspace.accounting.index'));

        $cash=Account::where('organization_id',$organization->id)->where('code','1000')->firstOrFail();
        $revenue=Account::where('organization_id',$organization->id)->where('code','4000')->firstOrFail();

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->post(route('workspace.accounting.journals.store'),[
                'journal_date'=>'2026-10-01',
                'reference'=>'JV-TEST',
                'description'=>'Service income received',
                'rows'=>[
                    ['account_id'=>$cash->id,'description'=>'Cash','debit'=>'1,000.00','credit'=>''],
                    ['account_id'=>$revenue->id,'description'=>'Revenue','debit'=>'','credit'=>'1,000.00'],
                ],
            ])
            ->assertRedirect(route('workspace.accounting.index'));

        $journal=Journal::firstOrFail();
        $this->assertSame('draft',$journal->status);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.accounting.journals.post',$journal))
            ->assertRedirect(route('workspace.accounting.index'));

        $journal->refresh();

        $this->assertSame('posted',$journal->status);
        $this->assertNotNull($journal->integrity_hash);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->get(route('workspace.accounting.index',[
                'from'=>'2026-10-01',
                'to'=>'2026-10-31',
            ]))
            ->assertOk()
            ->assertSee('JV-TEST')
            ->assertSee('1,000.00');

        $this->assertDatabaseHas('audit_events',[
            'organization_id'=>$organization->id,
            'action'=>'accounting.journal.posted',
        ]);
    }

    public function test_foreign_journal_cannot_be_posted(): void
    {
        $this->withoutVite();

        [$user,$organization]=$this->accountingUser();
        $foreign=Organization::create(['name'=>'Foreign Ledger','slug'=>'foreign-ledger']);
        $foreignJournal=Journal::create([
            'organization_id'=>$foreign->id,
            'number'=>'JV-FOREIGN-001',
            'journal_date'=>'2026-10-01',
            'status'=>'draft',
        ]);

        $this->actingAs($user)
            ->withSession(['current_organization_id'=>$organization->id])
            ->patch(route('workspace.accounting.journals.post',$foreignJournal))
            ->assertNotFound();
    }

    private function accountingUser(): array
    {
        $user=User::factory()->create();
        $organization=Organization::create(['name'=>'Accounting Workspace Co','slug'=>'accounting-workspace-co']);
        $user->organizations()->attach($organization,['status'=>'active','role'=>'member']);

        $view=Permission::firstOrCreate(['slug'=>'accounting.view'],['name'=>'Accounting View']);
        $manage=Permission::firstOrCreate(['slug'=>'accounting.manage'],['name'=>'Accounting Manage']);
        $role=Role::create(['name'=>'Accounting Manager','slug'=>'accounting-manager-test','scope'=>'organization']);
        $role->permissions()->attach([$view->id,$manage->id]);
        $user->roles()->attach($role,['organization_id'=>$organization->id]);

        return [$user,$organization];
    }
}
