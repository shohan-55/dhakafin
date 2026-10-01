<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaChallengeController;
use App\Http\Controllers\Auth\MfaSetupController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Tools\TaxCalendarController;
use App\Http\Controllers\Tools\WithholdingCalculatorController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\Workspace\AuditLogController;
use App\Http\Controllers\Workspace\DashboardController;
use App\Http\Controllers\Workspace\WithholdingTransactionController;
use App\Http\Controllers\Workspace\Mushak63Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tools/tds-vds-calculator', [WithholdingCalculatorController::class, 'create'])->name('withholding-calculator');
Route::post('/tools/tds-vds-calculator', [WithholdingCalculatorController::class, 'store'])->name('withholding-calculator.calculate');
Route::get('/tax-calendar', TaxCalendarController::class)->name('tax-calendar');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/mfa/challenge', [MfaChallengeController::class, 'create'])->name('mfa.challenge');
    Route::post('/mfa/challenge', [MfaChallengeController::class, 'store'])->name('mfa.challenge.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/settings/mfa', [MfaSetupController::class, 'create'])->name('mfa.setup');
    Route::post('/settings/mfa', [MfaSetupController::class, 'store'])->name('mfa.setup.store');
    Route::get('/settings/mfa/recovery-codes', [MfaSetupController::class, 'recoveryCodes'])->name('mfa.recovery-codes');

    Route::post('/workspace/switch/{organization}', OrganizationSwitchController::class)->name('workspace.switch');

    Route::middleware('tenant')->prefix('workspace')->name('workspace.')->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/audit-log', AuditLogController::class)
            ->middleware('permission:audit-log.view')
            ->name('audit-log');

        Route::get('/tax/withholding', [WithholdingTransactionController::class, 'index'])
            ->middleware('permission:tax.view')
            ->name('withholding.index');
        Route::get('/tax/withholding/create', [WithholdingTransactionController::class, 'create'])
            ->middleware('permission:tax.manage')
            ->name('withholding.create');
        Route::post('/tax/withholding', [WithholdingTransactionController::class, 'store'])
            ->middleware('permission:tax.manage')
            ->name('withholding.store');

        Route::get('/vat/mushak-6-3', [Mushak63Controller::class, 'index'])
            ->middleware('permission:vat.view')
            ->name('mushak63.index');
        Route::get('/vat/mushak-6-3/create', [Mushak63Controller::class, 'create'])
            ->middleware('permission:vat.manage')
            ->name('mushak63.create');
        Route::post('/vat/mushak-6-3', [Mushak63Controller::class, 'store'])
            ->middleware('permission:vat.manage')
            ->name('mushak63.store');
    });
});
