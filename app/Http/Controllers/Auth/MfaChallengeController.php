<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Services\TotpService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->session()->has('auth.mfa_pending_user_id'), 403);

        return view('auth.mfa-challenge');
    }

    public function store(Request $request, TotpService $totp, AuditLogger $audit): RedirectResponse
    {
        $request->validate(['code' => ['required','string','max:64']]);

        $user = User::findOrFail($request->session()->get('auth.mfa_pending_user_id'));
        $input = trim((string) $request->input('code'));
        $valid = $user->mfa_secret && $totp->verify($user->mfa_secret, preg_replace('/\s+/', '', $input));

        if (!$valid) {
            $hashes = $user->mfa_recovery_codes ?? [];
            foreach ($hashes as $index => $hash) {
                if (Hash::check($input, $hash)) {
                    unset($hashes[$index]);
                    $user->forceFill(['mfa_recovery_codes' => array_values($hashes)])->save();
                    $valid = true;
                    break;
                }
            }
        }

        if (!$valid) {
            throw ValidationException::withMessages(['code' => 'The authentication code is invalid.']);
        }

        $remember = (bool) $request->session()->pull('auth.mfa_remember', false);
        $request->session()->forget('auth.mfa_pending_user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $audit->record('auth.mfa_verified', $user, request: $request);

        return redirect()->intended(route('workspace.dashboard'));
    }
}
