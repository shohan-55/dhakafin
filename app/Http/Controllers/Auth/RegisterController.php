<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Support\Auditing\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:users,email'],
            'organization_name' => ['required','string','max:190'],
            'password' => ['required','confirmed', Password::min(10)->letters()->mixedCase()->numbers()],
        ]);

        [$user, $organization] = DB::transaction(function () use ($validated): array {
            $user = User::create([
                'name' => $validated['name'],
                'email' => Str::lower($validated['email']),
                'password' => $validated['password'],
            ]);

            $organization = Organization::create([
                'name' => $validated['organization_name'],
                'legal_name' => $validated['organization_name'],
                'slug' => Str::slug($validated['organization_name']).'-'.Str::lower(Str::random(6)),
                'status' => 'active',
                'timezone' => 'Asia/Dhaka',
            ]);

            $user->organizations()->attach($organization, [
                'role' => 'owner',
                'status' => 'active',
            ]);

            $ownerRole = Role::where('slug', 'owner')->firstOrFail();
            $user->roles()->attach($ownerRole, ['organization_id' => $organization->getKey()]);

            return [$user, $organization];
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('current_organization_id', $organization->getKey());

        $audit->record(
            action: 'account.registered',
            actor: $user,
            organization: $organization,
            auditable: $organization,
            request: $request,
        );

        return redirect()->route('workspace.dashboard');
    }
}
