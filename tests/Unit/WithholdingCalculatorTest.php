<?php

namespace Tests\Unit;

use App\Domain\TaxVat\Services\WithholdingCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WithholdingCalculatorTest extends TestCase
{
    public function test_it_calculates_withholding_using_integer_minor_units(): void
    {
        $result = (new WithholdingCalculator())->calculate(100000, '10');

        $this->assertSame(10000, $result['withheld_amount_minor']);
        $this->assertSame(90000, $result['net_amount_minor']);
        $this->assertSame('10.0000', $result['rate_percent']);
    }

    public function test_it_rejects_rates_over_one_hundred_percent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new WithholdingCalculator())->calculate(100000, '100.0001');
    }
}
