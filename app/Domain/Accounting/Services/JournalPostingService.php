<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Journal;
use App\Models\Organization;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class JournalPostingService
{
    public function createDraft(
        Organization $organization,
        string $journalDate,
        array $lines,
        ?User $creator=null,
        string $source='manual',
        ?string $reference=null,
        ?string $description=null,
    ): Journal {
        if (count($lines)<2) {
            throw new DomainException('A journal requires at least two lines.');
        }

        return DB::transaction(function () use (
            $organization,$journalDate,$lines,$creator,$source,$reference,$description
        ): Journal {
            do {
                $number='JV-'.date('ymd',strtotime($journalDate)).'-'.Str::upper(Str::random(6));
            } while (Journal::where('organization_id',$organization->id)->where('number',$number)->exists());

            $normalized=[];

            foreach ($lines as $line) {
                $account=Account::query()
                    ->where('organization_id',$organization->id)
                    ->where('is_active',true)
                    ->where('is_postable',true)
                    ->find($line['account_id'] ?? 0);

                if (!$account) {
                    throw new DomainException('Each journal line must use an active postable account from the current organization.');
                }

                $debit=(int)($line['debit_minor'] ?? 0);
                $credit=(int)($line['credit_minor'] ?? 0);

                if ($debit<0 || $credit<0 || ($debit===0 && $credit===0) || ($debit>0 && $credit>0)) {
                    throw new DomainException('Each journal line must contain either a positive debit or a positive credit.');
                }

                $normalized[]=[
                    'account_id'=>$account->id,
                    'description'=>trim((string)($line['description'] ?? '')) ?: null,
                    'debit_minor'=>$debit,
                    'credit_minor'=>$credit,
                ];
            }

            $debits=array_sum(array_column($normalized,'debit_minor'));
            $credits=array_sum(array_column($normalized,'credit_minor'));

            if ($debits!==$credits) {
                throw new DomainException('Journal debits and credits must balance exactly.');
            }

            $journal=Journal::create([
                'organization_id'=>$organization->id,
                'number'=>$number,
                'journal_date'=>$journalDate,
                'source'=>$source,
                'reference'=>$reference,
                'description'=>$description,
                'status'=>'draft',
                'created_by'=>$creator?->id,
            ]);

            $journal->lines()->createMany($normalized);

            return $journal->load('lines.account');
        });
    }

    public function post(Journal $journal, User $poster): Journal
    {
        return DB::transaction(function () use ($journal,$poster): Journal {
            $journal=Journal::query()->lockForUpdate()->findOrFail($journal->id);

            if ($journal->status!=='draft') {
                throw new DomainException('Only draft journals can be posted.');
            }

            $closed=AccountingPeriod::query()
                ->where('organization_id',$journal->organization_id)
                ->where('status','closed')
                ->whereDate('starts_on','<=',$journal->journal_date)
                ->whereDate('ends_on','>=',$journal->journal_date)
                ->exists();

            if ($closed) {
                throw new DomainException('The journal date falls within a closed accounting period.');
            }

            $lines=$journal->lines()->with('account')->orderBy('id')->lockForUpdate()->get();

            if ($lines->count()<2) {
                throw new DomainException('A journal requires at least two lines.');
            }

            $debits=0;
            $credits=0;

            foreach ($lines as $line) {
                if ($line->account->organization_id!==$journal->organization_id || !$line->account->is_active || !$line->account->is_postable) {
                    throw new DomainException('Journal contains an invalid account.');
                }

                if (($line->debit_minor>0 && $line->credit_minor>0) || ($line->debit_minor===0 && $line->credit_minor===0)) {
                    throw new DomainException('Journal line debit/credit shape is invalid.');
                }

                $debits+=$line->debit_minor;
                $credits+=$line->credit_minor;
            }

            if ($debits!==$credits) {
                throw new DomainException('Journal debits and credits must balance exactly.');
            }

            $payload=[
                'organization_id'=>$journal->organization_id,
                'number'=>$journal->number,
                'journal_date'=>$journal->journal_date->toDateString(),
                'source'=>$journal->source,
                'reference'=>$journal->reference,
                'lines'=>$lines->map(fn($line)=>[
                    'account_id'=>$line->account_id,
                    'debit_minor'=>$line->debit_minor,
                    'credit_minor'=>$line->credit_minor,
                    'description'=>$line->description,
                ])->all(),
            ];

            $journal->forceFill([
                'status'=>'posted',
                'posted_by'=>$poster->id,
                'posted_at'=>now(),
                'integrity_hash'=>hash('sha256',json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)),
            ])->save();

            return $journal->refresh()->load('lines.account');
        });
    }
}
