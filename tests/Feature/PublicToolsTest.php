<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_withholding_calculator_returns_exact_bdt_values(): void
    {
        $this->withoutVite();

        $this->post(route('withholding-calculator.calculate'), [
            'kind' => 'tds',
            'amount' => '100000.00',
            'rate' => '10',
        ])
            ->assertOk()
            ->assertSee('TDS withholding')
            ->assertSee('৳ 10,000.00')
            ->assertSee('৳ 90,000.00');
    }

    public function test_tax_calendar_hides_unverified_rules(): void
    {
        $this->withoutVite();

        \App\Models\ComplianceRule::create([
            'code' => 'UNVERIFIED-DEMO',
            'category' => 'tax',
            'title' => 'Unverified demo rule',
            'frequency' => 'monthly',
            'rule_data' => ['day' => 15],
            'is_active' => true,
        ]);

        $this->get(route('tax-calendar'))
            ->assertOk()
            ->assertDontSee('Unverified demo rule')
            ->assertSee('Verification dataset is being prepared');
    }
}
