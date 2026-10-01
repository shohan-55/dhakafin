<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        return view('workspace.dashboard', [
            'organization' => $organization,
            'openObligations' => $organization->complianceObligations()
                ->where('status', 'open')
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
        ]);
    }
}
