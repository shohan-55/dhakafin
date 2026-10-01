<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('dhakafin:status', function (): void {
    $this->info('DhakaFin application foundation is healthy.');
})->purpose('Verify the DhakaFin application bootstraps');
