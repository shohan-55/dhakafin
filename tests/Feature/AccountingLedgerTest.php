<?php

namespace Tests\Feature;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\TrialBalanceService;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Organization;
use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_balanced_journal_posts_with_integrity_hash_and_feeds_trial_balance(): void
    {
        $organization=Organization::create(['name'=>'Ledger Co','slug'=>'ledger-co']);
        $user=User::factory()->create();

        $cash=$this->account($organization,'1000','Cash','asset','debit');
        $revenue=$this->account($organization,'4000','Revenue','revenue','credit');

        $journal=app(JournalPostingService::class)->createDraft(
            $organization,
            '2026-10-01',
            [
                ['account_id'=>$cash->id,'debit_minor'=>100000,'credit_minor'=>0,'description'=>'Cash received'],
                ['account_id'=>$revenue->id,'debit_minor'=>0,'credit_minor'=>100000,'description'=>'Service revenue'],
            ],
            $user,
            'manual',
            'REF-001',
            'Cash service revenue',
        );

        $this->assertSame('draft',$journal->status);

        $journal=app(JournalPostingService::class)->post($journal,$user);

        $this->assertSame('posted',$journal->status);
        $this->assertNotNull($journal->integrity_hash);
        $this->assertSame(64,strlen($journal->integrity_hash));

        $trial=app(TrialBalanceService::class)->forPeriod($organization,'2026-10-01','2026-10-31');

        $cashRow=$trial->firstWhere('code','1000');
        $revenueRow=$trial->firstWhere('code','4000');

        $this->assertSame(100000,$cashRow->debit_minor);
        $this->assertSame(0,$cashRow->credit_minor);
        $this->assertSame(0,$revenueRow->debit_minor);
        $this->assertSame(100000,$revenueRow->credit_minor);
    }

    public function test_unbalanced_journal_is_rejected(): void
    {
        $organization=Organization::create(['name'=>'Unbalanced Co','slug'=>'unbalanced-co']);
        $cash=$this->account($organization,'1000','Cash','asset','debit');
        $revenue=$this->account($organization,'4000','Revenue','revenue','credit');

        $this->expectException(DomainException::class);

        app(JournalPostingService::class)->createDraft(
            $organization,
            '2026-10-01',
            [
                ['account_id'=>$cash->id,'debit_minor'=>100000,'credit_minor'=>0],
                ['account_id'=>$revenue->id,'debit_minor'=>0,'credit_minor'=>99999],
            ],
        );
    }

    public function test_closed_period_blocks_posting_and_posted_journal_is_immutable(): void
    {
        $organization=Organization::create(['name'=>'Period Co','slug'=>'period-co']);
        $user=User::factory()->create();

        $cash=$this->account($organization,'1000','Cash','asset','debit');
        $equity=$this->account($organization,'3000','Equity','equity','credit');

        $journal=app(JournalPostingService::class)->createDraft(
            $organization,
            '2026-10-01',
            [
                ['account_id'=>$cash->id,'debit_minor'=>50000,'credit_minor'=>0],
                ['account_id'=>$equity->id,'debit_minor'=>0,'credit_minor'=>50000],
            ],
        );

        AccountingPeriod::create([
            'organization_id'=>$organization->id,
            'starts_on'=>'2026-10-01',
            'ends_on'=>'2026-10-31',
            'status'=>'closed',
        ]);

        try {
            app(JournalPostingService::class)->post($journal,$user);
            $this->fail('Closed period should block posting.');
        } catch (DomainException) {
            $this->assertSame('draft',$journal->fresh()->status);
        }

        AccountingPeriod::query()->delete();
        $journal=app(JournalPostingService::class)->post($journal,$user);

        $this->expectException(DomainException::class);
        $journal->forceFill(['description'=>'Tampered'])->save();
    }

    private function account(
        Organization $organization,
        string $code,
        string $name,
        string $type,
        string $normal,
    ): Account {
        return Account::create([
            'organization_id'=>$organization->id,
            'code'=>$code,
            'name'=>$name,
            'type'=>$type,
            'normal_balance'=>$normal,
            'is_postable'=>true,
            'is_active'=>true,
        ]);
    }
}
