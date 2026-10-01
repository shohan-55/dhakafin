<?php

namespace App\Http\Controllers\Workspace;

use App\Domain\TaxVat\Services\Mushak63DraftService;
use App\Http\Controllers\Controller;
use App\Models\MushakForm;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class Mushak63Controller extends Controller
{
    public function index(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        return view('workspace.mushak63.index', [
            'organization' => $organization,
            'forms' => MushakForm::query()
                ->where('organization_id', $organization->getKey())
                ->where('form_type', '6.3')
                ->orderByDesc('issue_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function create(TenantContext $tenants): View
    {
        return view('workspace.mushak63.create', [
            'organization' => $tenants->current(),
        ]);
    }

    public function store(
        Request $request,
        TenantContext $tenants,
        Mushak63DraftService $drafts,
        AuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'serial_no' => ['nullable','string','max:100'],
            'issue_date' => ['required','date'],
            'buyer_name' => ['nullable','string','max:190'],
            'buyer_bin' => ['nullable','string','max:50'],
            'buyer_address' => ['nullable','string','max:1000'],
            'rows' => ['required','array','min:1','max:20'],
            'rows.*.description' => ['nullable','string','max:500'],
            'rows.*.quantity' => ['nullable','string','max:30'],
            'rows.*.value' => ['nullable','string','max:30'],
            'rows.*.vat' => ['nullable','string','max:30'],
        ]);

        try {
            $draft = $drafts->build($validated['rows']);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['rows' => $exception->getMessage()]);
        }

        $form = MushakForm::create([
            'organization_id' => $tenants->id(),
            'form_type' => '6.3',
            'serial_no' => $validated['serial_no'] ?? null,
            'issue_date' => $validated['issue_date'],
            'buyer_name' => $validated['buyer_name'] ?? null,
            'buyer_bin' => $validated['buyer_bin'] ?? null,
            'buyer_address' => $validated['buyer_address'] ?? null,
            'lines' => $draft['lines'],
            'total_value_minor' => $draft['total_value_minor'],
            'total_vat_minor' => $draft['total_vat_minor'],
            'status' => 'draft',
            'created_by' => $request->user()->getKey(),
        ]);

        $audit->record(
            action: 'vat.mushak63.draft_created',
            actor: $request->user(),
            organization: $tenants->current(),
            auditable: $form,
            request: $request,
        );

        return redirect()
            ->route('workspace.mushak63.index')
            ->with('status', 'Mushak 6.3 draft saved.');
    }
}
