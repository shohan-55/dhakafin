<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function __construct(private readonly TenantContext $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $organizationId = $request->session()->get('current_organization_id');

        $query = $user->organizations()
            ->wherePivot('status', 'active')
            ->where('organizations.status', 'active');

        $organization = $organizationId
            ? (clone $query)->whereKey($organizationId)->first()
            : $query->orderBy('organizations.id')->first();

        abort_unless($organization, 403, 'No active organization is available.');

        $request->session()->put('current_organization_id', $organization->getKey());
        $this->tenants->set($organization);

        try {
            return $next($request);
        } finally {
            $this->tenants->clear();
        }
    }
}
