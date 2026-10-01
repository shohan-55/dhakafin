<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_identifies_dhakafin_and_core_services(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('DhakaFin')
            ->assertSee('Accounting & Bookkeeping')
            ->assertSee('Statutory Audit Support')
            ->assertSee('TDS / VDS Calculator');
    }
}
