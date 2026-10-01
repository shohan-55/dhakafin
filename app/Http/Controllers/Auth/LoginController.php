<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again shortly.',
            ]);
        }

        $provider = Auth::getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (!$user instanceof User || !$provider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        RateLimiter::clear($key);

        if ($user->mfa_enabled_at) {
            $request->session()->put([
                'auth.mfa_pending_user_id' => $user->getKey(),
                'auth.mfa_remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('mfa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $audit->record('auth.login', $user, request: $request);

        return redirect()->intended(route('workspace.dashboard'));
    }

    public function destroy(Request $request, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $audit->record('auth.logout', $user, request: $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
