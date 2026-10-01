<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function runFor(Organization $organization): void
    {
        $accounts=[
            ['1000','Cash & Cash Equivalents','asset','debit'],
            ['1100','Accounts Receivable','asset','debit'],
            ['1200','Inventory','asset','debit'],
            ['1300','Prepayments & Other Current Assets','asset','debit'],
            ['1500','Property, Plant & Equipment','asset','debit'],
            ['2000','Accounts Payable','liability','credit'],
            ['2100','Tax & VAT Payable','liability','credit'],
            ['2200','Accrued Expenses','liability','credit'],
            ['3000','Share Capital / Owner Equity','equity','credit'],
            ['3100','Retained Earnings','equity','credit'],
            ['4000','Service Revenue','revenue','credit'],
            ['4100','Other Operating Income','revenue','credit'],
            ['5000','Cost of Services','expense','debit'],
            ['6000','Salaries & Benefits','expense','debit'],
            ['6100','Rent & Utilities','expense','debit'],
            ['6200','Professional & Admin Expenses','expense','debit'],
            ['6300','Depreciation','expense','debit'],
            ['6400','Finance Cost','expense','debit'],
            ['6500','Income Tax Expense','expense','debit'],
        ];

        foreach($accounts as [$code,$name,$type,$normal]){
            Account::firstOrCreate(
                ['organization_id'=>$organization->id,'code'=>$code],
                ['name'=>$name,'type'=>$type,'normal_balance'=>$normal,'is_postable'=>true,'is_active'=>true]
            );
        }
    }
}
