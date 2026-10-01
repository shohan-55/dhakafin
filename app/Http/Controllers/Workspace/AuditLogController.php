<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;

class AuditLogController extends Controller
{
    public function __invoke(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        $events = $organization->auditEvents()
            ->with('actor:id,name,email')
            ->orderByDesc('occurred_at')
            ->limit(100)
            ->get();

        return view('workspace.audit-log', compact('organization', 'events'));
    }
}
