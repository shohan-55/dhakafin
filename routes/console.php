<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('dhakafin:status', function (): void {
    $this->info('DhakaFin application foundation is healthy.');
})->purpose('Verify the DhakaFin application bootstraps');

Schedule::command('dhakafin:compliance-generate')->dailyAt('00:15')->withoutOverlapping();
