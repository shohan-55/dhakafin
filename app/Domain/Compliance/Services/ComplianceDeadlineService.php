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

        if ($rule->frequency === 'monthly') {
            $target = $periodStart
                ->startOfMonth()
                ->addMonths((int) ($data['month_offset'] ?? 0));

            return $target->day(min(
                $this->validatedDay($data['day'] ?? null),
                $target->daysInMonth
            ));
        }

        if ($rule->frequency === 'annual') {
            $month = filter_var($data['month'] ?? null, FILTER_VALIDATE_INT);
            if ($month === false || $month < 1 || $month > 12) {
                throw new InvalidArgumentException('Annual rule month must be between 1 and 12.');
            }

            $target = CarbonImmutable::create(
                $periodStart->year + (int) ($data['year_offset'] ?? 0),
                $month,
                1,
                0, 0, 0,
                'Asia/Dhaka'
            );

            return $target->day(min(
                $this->validatedDay($data['day'] ?? null),
                $target->daysInMonth
            ));
        }

        throw new InvalidArgumentException('Unsupported compliance rule frequency.');
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
                'compliance_rule_id' => $rule->getKey(),
                'period_key' => $periodKey,
            ],
            [
                'type' => $rule->category,
                'title' => $rule->title,
                'due_date' => $dueDate->toDateString(),
                'status' => 'open',
                'metadata' => ['rule_code' => $rule->code],
            ]
        );
    }

    private function validatedDay(mixed $day): int
    {
        $day = filter_var($day, FILTER_VALIDATE_INT);
        if ($day === false || $day < 1 || $day > 31) {
            throw new InvalidArgumentException('Rule day must be between 1 and 31.');
        }

        return $day;
    }
}
