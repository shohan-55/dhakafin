<?php

namespace App\Http\Controllers\Workspace;

use App\Domain\Accounting\Services\JournalPostingService;
use App\Domain\Accounting\Services\TrialBalanceService;
use App\Domain\TaxVat\Services\BdtMoney;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\ChartOfAccountsSeeder;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class AccountingController extends Controller
{
    public function index(
        Request $request,
        TenantContext $tenants,
        TrialBalanceService $trialBalance,
    ): View {
        $organization=$tenants->current();
        $from=$request->string('from')->toString() ?: now('Asia/Dhaka')->startOfYear()->toDateString();
        $to=$request->string('to')->toString() ?: now('Asia/Dhaka')->toDateString();

        return view('workspace.accounting.index',[
            'organization'=>$organization,
            'accounts'=>Account::query()
                ->where('organization_id',$organization->id)
                ->where('is_active',true)
                ->orderBy('code')
                ->get(),
            'journals'=>Journal::query()
                ->where('organization_id',$organization->id)
                ->orderByDesc('journal_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
            'trialBalance'=>$trialBalance->forPeriod($organization,$from,$to),
            'from'=>$from,
            'to'=>$to,
        ]);
    }

    public function bootstrapAccounts(
        Request $request,
        TenantContext $tenants,
        ChartOfAccountsSeeder $seeder,
        AuditLogger $audit,
    ): RedirectResponse {
        $seeder->runFor($tenants->current());

        $audit->record(
            'accounting.chart_bootstrapped',
            $request->user(),
            $tenants->current(),
            metadata:['account_count'=>Account::where('organization_id',$tenants->id())->count()],
            request:$request,
        );

        return redirect()->route('workspace.accounting.index')->with('status','Chart of accounts is ready.');
    }

    public function createJournal(TenantContext $tenants): View
    {
        $organization=$tenants->current();

        return view('workspace.accounting.journal-create',[
            'organization'=>$organization,
            'accounts'=>Account::query()
                ->where('organization_id',$organization->id)
                ->where('is_active',true)
                ->where('is_postable',true)
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function storeJournal(
        Request $request,
        TenantContext $tenants,
        BdtMoney $money,
        JournalPostingService $posting,
        AuditLogger $audit,
    ): RedirectResponse {
        $validated=$request->validate([
            'journal_date'=>['required','date'],
            'reference'=>['nullable','string','max:190'],
            'description'=>['nullable','string','max:2000'],
            'rows'=>['required','array','min:2','max:30'],
            'rows.*.account_id'=>['nullable','integer'],
            'rows.*.description'=>['nullable','string','max:500'],
            'rows.*.debit'=>['nullable','string','max:30'],
            'rows.*.credit'=>['nullable','string','max:30'],
        ]);

        try {
            $lines=[];

            foreach($validated['rows'] as $row){
                $accountId=(int)($row['account_id'] ?? 0);
                $debit=trim((string)($row['debit'] ?? ''));
                $credit=trim((string)($row['credit'] ?? ''));

                if($accountId===0 && $debit==='' && $credit===''){
                    continue;
                }

                $lines[]=[
                    'account_id'=>$accountId,
                    'description'=>$row['description'] ?? null,
                    'debit_minor'=>$debit==='' ? 0 : $money->parseToMinor($debit),
                    'credit_minor'=>$credit==='' ? 0 : $money->parseToMinor($credit),
                ];
            }

            $journal=$posting->createDraft(
                $tenants->current(),
                $validated['journal_date'],
                $lines,
                $request->user(),
                'manual',
                $validated['reference'] ?? null,
                $validated['description'] ?? null,
            );
        } catch(InvalidArgumentException|DomainException $exception){
            throw ValidationException::withMessages(['rows'=>$exception->getMessage()]);
        }

        $audit->record(
            'accounting.journal.created',
            $request->user(),
            $tenants->current(),
            $journal,
            request:$request,
        );

        return redirect()->route('workspace.accounting.index')->with('status','Journal draft created.');
    }

    public function postJournal(
        Request $request,
        Journal $journal,
        TenantContext $tenants,
        JournalPostingService $posting,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($journal->organization_id===$tenants->id(),404);

        try {
            $posting->post($journal,$request->user());
        } catch(DomainException $exception){
            throw ValidationException::withMessages(['journal'=>$exception->getMessage()]);
        }

        $audit->record(
            'accounting.journal.posted',
            $request->user(),
            $tenants->current(),
            $journal,
            metadata:['integrity_hash'=>$journal->fresh()->integrity_hash],
            request:$request,
        );

        return redirect()->route('workspace.accounting.index')->with('status','Journal posted.');
    }
}
