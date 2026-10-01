<?php

namespace App\Console\Commands;

use App\Domain\Compliance\Services\ComplianceDeadlineService;
use App\Models\ComplianceRule;
use App\Models\Organization;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateComplianceObligations extends Command
{
    protected $signature = 'dhakafin:compliance-generate
        {--month= : Period month in YYYY-MM format}
        {--organization= : Limit to one organization ID}';

    protected $description = 'Generate idempotent monthly compliance obligations from verified rules';

    public function handle(ComplianceDeadlineService $deadlines): int
    {
        $period = $this->option('month')
            ? CarbonImmutable::createFromFormat('Y-m-d', $this->option('month').'-01', 'Asia/Dhaka')
            : CarbonImmutable::now('Asia/Dhaka')->startOfMonth();

        if (!$period) {
            $this->error('Invalid --month value. Use YYYY-MM.');
            return self::FAILURE;
        }

        $organizations = Organization::query()
            ->where('status', 'active')
            ->when($this->option('organization'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $rules = ComplianceRule::query()
            ->published()
            ->where('frequency', 'monthly')
            ->where(function ($q) use ($period): void {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $period->endOfMonth()->toDateString());
            })
            ->where(function ($q) use ($period): void {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $period->startOfMonth()->toDateString());
            })
            ->get();

        $created = 0;

        foreach ($organizations as $organization) {
            foreach ($rules as $rule) {
                $obligation = $deadlines->generateMonthly($organization, $rule, $period);

                if ($obligation->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $this->info("Generated {$created} new compliance obligation(s).");

        return self::SUCCESS;
    }
}
