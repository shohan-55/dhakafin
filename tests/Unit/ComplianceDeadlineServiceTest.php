<?php

namespace Tests\Unit;

use App\Domain\Compliance\Services\ComplianceDeadlineService;
use App\Models\ComplianceRule;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ComplianceDeadlineServiceTest extends TestCase
{
    public function test_monthly_rule_can_clamp_day_31_to_month_end(): void
    {
        $rule = new ComplianceRule([
            'frequency' => 'monthly',
            'rule_data' => ['day' => 31, 'month_offset' => 0],
        ]);

        $due = (new ComplianceDeadlineService())->dueDate(
            $rule,
            CarbonImmutable::parse('2027-02-01', 'Asia/Dhaka')
        );

        $this->assertSame('2027-02-28', $due->toDateString());
    }
}
