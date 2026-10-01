<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Support\Auditing\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationSwitchController extends Controller
{
    public function __invoke(Request $request, Organization $organization, AuditLogger $audit): RedirectResponse
    {
        $allowed = $request->user()
            ->organizations()
            ->wherePivot('status', 'active')
            ->whereKey($organization->getKey())
            ->exists();

        abort_unless($allowed && $organization->status === 'active', 403);

        $request->session()->put('current_organization_id', $organization->getKey());

        $audit->record(
            action: 'organization.switched',
            actor: $request->user(),
            organization: $organization,
            auditable: $organization,
            request: $request,
        );

        return redirect()->route('workspace.dashboard');
    }
}
