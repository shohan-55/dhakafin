<?php

namespace Tests\Unit;

use App\Domain\TaxVat\Services\BdtMoney;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BdtMoneyTest extends TestCase
{
    public function test_it_parses_and_formats_bdt_without_floating_point_math(): void
    {
        $money = new BdtMoney();

        $this->assertSame(12345678, $money->parseToMinor('123,456.78'));
        $this->assertSame('123,456.78', $money->formatMinor(12345678));
    }

    public function test_it_rejects_more_than_two_decimal_places(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BdtMoney())->parseToMinor('10.999');
    }
}
