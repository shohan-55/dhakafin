<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\ComplianceObligation;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceObligationController extends Controller
{
    public function index(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        return view('workspace.compliance.index', [
            'organization' => $organization,
            'obligations' => ComplianceObligation::query()
                ->where('organization_id', $organization->getKey())
                ->with(['rule','responsibleUser:id,name,email'])
                ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
                ->orderBy('due_date')
                ->limit(200)
                ->get(),
        ]);
    }

    public function complete(
        Request $request,
        ComplianceObligation $obligation,
        TenantContext $tenants,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($obligation->organization_id === $tenants->id(), 404);

        $validated = $request->validate([
            'evidence_reference' => ['nullable','string','max:190'],
            'notes' => ['nullable','string','max:2000'],
        ]);

        $obligation->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'evidence_reference' => $validated['evidence_reference'] ?? null,
            'notes' => $validated['notes'] ?? $obligation->notes,
        ])->save();

        $audit->record(
            action: 'compliance.obligation.completed',
            actor: $request->user(),
            organization: $tenants->current(),
            auditable: $obligation,
            metadata: ['period_key' => $obligation->period_key],
            request: $request,
        );

        return redirect()
            ->route('workspace.compliance.index')
            ->with('status', 'Compliance obligation marked complete.');
    }
}
