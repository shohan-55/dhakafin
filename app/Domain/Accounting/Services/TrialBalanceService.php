<?php

namespace App\Domain\Accounting\Services;

use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TrialBalanceService
{
    public function forPeriod(Organization $organization, string $from, string $to): Collection
    {
        return DB::table('accounts')
            ->leftJoin('journal_lines','accounts.id','=','journal_lines.account_id')
            ->leftJoin('journals',function($join) use ($from,$to){
                $join->on('journal_lines.journal_id','=','journals.id')
                    ->where('journals.status','=','posted')
                    ->whereBetween('journals.journal_date',[$from,$to]);
            })
            ->where('accounts.organization_id',$organization->id)
            ->where('accounts.is_active',true)
            ->groupBy('accounts.id','accounts.code','accounts.name','accounts.type','accounts.normal_balance')
            ->orderBy('accounts.code')
            ->select([
                'accounts.id','accounts.code','accounts.name','accounts.type','accounts.normal_balance',
                DB::raw('COALESCE(SUM(CASE WHEN journals.id IS NOT NULL THEN journal_lines.debit_minor ELSE 0 END),0) as debit_minor'),
                DB::raw('COALESCE(SUM(CASE WHEN journals.id IS NOT NULL THEN journal_lines.credit_minor ELSE 0 END),0) as credit_minor'),
            ])
            ->get()
            ->map(function($row){
                $row->debit_minor=(int)$row->debit_minor;
                $row->credit_minor=(int)$row->credit_minor;
                $row->net_minor=$row->debit_minor-$row->credit_minor;
                return $row;
            });
    }
}
