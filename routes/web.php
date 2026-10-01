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
use App\Http\Controllers\Workspace\ComplianceObligationController;
use App\Http\Controllers\Workspace\WithholdingTransactionController;
use App\Http\Controllers\Workspace\Mushak63Controller;
use App\Http\Controllers\Workspace\EngagementController;
use App\Http\Controllers\Workspace\DocumentController;
use App\Http\Controllers\Workspace\DocumentReviewController;
use App\Http\Controllers\Workspace\BillingController;
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

        Route::get('/compliance', [ComplianceObligationController::class, 'index'])
            ->middleware('permission:compliance.view')
            ->name('compliance.index');
        Route::patch('/compliance/{obligation}/complete', [ComplianceObligationController::class, 'complete'])
            ->middleware('permission:compliance.manage')
            ->name('compliance.complete');

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

        Route::get('/engagements', [EngagementController::class, 'index'])
            ->middleware('permission:engagements.view')
            ->name('engagements.index');
        Route::get('/engagements/create', [EngagementController::class, 'create'])
            ->middleware('permission:engagements.manage')
            ->name('engagements.create');
        Route::post('/engagements', [EngagementController::class, 'store'])
            ->middleware('permission:engagements.manage')
            ->name('engagements.store');
        Route::get('/engagements/{engagement}', [EngagementController::class, 'show'])
            ->middleware('permission:engagements.view')
            ->name('engagements.show');
        Route::patch('/engagements/{engagement}/advance', [EngagementController::class, 'advance'])
            ->middleware('permission:engagements.manage')
            ->name('engagements.advance');

        Route::post('/engagements/{engagement}/documents', [DocumentController::class, 'store'])
            ->middleware('permission:documents.manage')
            ->name('engagements.documents.store');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
            ->middleware('permission:documents.view')
            ->name('documents.download');
        Route::post('/documents/{document}/review', [DocumentReviewController::class, 'store'])
            ->middleware('permission:documents.review')
            ->name('documents.review');

        Route::get('/billing', [BillingController::class, 'index'])
            ->middleware('permission:billing.view')
            ->name('billing.index');
        Route::get('/billing/invoices/create', [BillingController::class, 'createInvoice'])
            ->middleware('permission:billing.manage')
            ->name('billing.invoices.create');
        Route::post('/billing/invoices', [BillingController::class, 'storeInvoice'])
            ->middleware('permission:billing.manage')
            ->name('billing.invoices.store');
        Route::patch('/billing/invoices/{invoice}/issue', [BillingController::class, 'issue'])
            ->middleware('permission:billing.manage')
            ->name('billing.invoices.issue');
        Route::post('/billing/payments', [BillingController::class, 'storePayment'])
            ->middleware('permission:billing.manage')
            ->name('billing.payments.store');
    });
});
