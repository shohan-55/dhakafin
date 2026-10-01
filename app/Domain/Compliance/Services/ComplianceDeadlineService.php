<?php

namespace App\Domain\Compliance\Services;

use App\Models\ComplianceObligation;
use App\Models\ComplianceRule;
use App\Models\Organization;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class ComplianceDeadlineService
{
    public function dueDate(ComplianceRule $rule, CarbonImmutable $periodStart): CarbonImmutable
    {
        $data = $rule->rule_data ?? [];

        return match ($rule->frequency) {
            'monthly' => $periodStart
                ->startOfMonth()
                ->addMonths((int) ($data['month_offset'] ?? 0))
                ->day($this->boundedDay($data['day'] ?? null)),
            'annual' => CarbonImmutable::create(
                $periodStart->year + (int) ($data['year_offset'] ?? 0),
                (int) ($data['month'] ?? 1),
                $this->boundedDay($data['day'] ?? null),
                0, 0, 0,
                'Asia/Dhaka'
            ),
            default => throw new InvalidArgumentException('Unsupported compliance rule frequency.'),
        };
    }

    public function generateMonthly(
        Organization $organization,
        ComplianceRule $rule,
        CarbonImmutable $periodStart
    ): ComplianceObligation {
        if ($rule->frequency !== 'monthly') {
            throw new InvalidArgumentException('Rule is not monthly.');
        }

        $periodKey = $periodStart->format('Y-m');
        $dueDate = $this->dueDate($rule, $periodStart);

        return ComplianceObligation::firstOrCreate(
            [
                'organization_id' => $organization->getKey(),
                'type' => $rule->category,
                'period_key' => $periodKey,
            ],
            [
                'compliance_rule_id' => $rule->getKey(),
                'title' => $rule->title,
                'due_date' => $dueDate->toDateString(),
                'status' => 'open',
                'metadata' => ['rule_code' => $rule->code],
            ]
        );
    }

    private function boundedDay(mixed $day): int
    {
        $day = filter_var($day, FILTER_VALIDATE_INT);
        if ($day === false || $day < 1 || $day > 28) {
            throw new InvalidArgumentException('Rule day must be between 1 and 28 for deterministic monthly scheduling.');
        }

        return $day;
    }
}
