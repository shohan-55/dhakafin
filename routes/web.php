<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaChallengeController;
use App\Http\Controllers\Auth\MfaSetupController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\Workspace\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

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
    });
});
