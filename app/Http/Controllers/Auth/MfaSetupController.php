<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Services\TotpService;
use App\Http\Controllers\Controller;
use App\Support\Auditing\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaSetupController extends Controller
{
    public function create(Request $request, TotpService $totp): View
    {
        $secret = $request->session()->get('mfa_setup_secret');

        if (!$secret) {
            $secret = $totp->generateSecret();
            $request->session()->put('mfa_setup_secret', $secret);
        }

        $issuer = rawurlencode(config('app.name', 'DhakaFin'));
        $label = rawurlencode($request->user()->email);
        $uri = "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&period=30&digits=6";

        return view('auth.mfa-setup', compact('secret', 'uri'));
    }

    public function store(Request $request, TotpService $totp, AuditLogger $audit): RedirectResponse
    {
        $request->validate(['code' => ['required','digits:6']]);

        $secret = (string) $request->session()->get('mfa_setup_secret');
        if (!$secret || !$totp->verify($secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages(['code' => 'The code could not be verified.']);
        }

        $plainCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->values();

        $user = $request->user();
        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_recovery_codes' => $plainCodes->map(fn (string $code) => Hash::make($code))->all(),
            'mfa_enabled_at' => now(),
        ])->save();

        $request->session()->forget('mfa_setup_secret');
        $request->session()->put('mfa_recovery_codes_once', $plainCodes->all());

        $audit->record('auth.mfa_enabled', $user, request: $request);

        return redirect()->route('mfa.recovery-codes');
    }

    public function recoveryCodes(Request $request): View|RedirectResponse
    {
        $codes = $request->session()->pull('mfa_recovery_codes_once');

        return $codes
            ? view('auth.mfa-recovery-codes', compact('codes'))
            : redirect()->route('workspace.dashboard');
    }
}
