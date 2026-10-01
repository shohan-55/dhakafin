<?php

namespace App\Http\Controllers\Workspace;

use App\Domain\Engagements\Services\EngagementWorkflow;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function index(TenantContext $tenants): View
    {
        $organization = $tenants->current();

        return view('workspace.engagements.index', [
            'organization'=>$organization,
            'engagements'=>Engagement::query()
                ->where('organization_id',$organization->id)
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('due_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function create(TenantContext $tenants): View
    {
        return view('workspace.engagements.create', ['organization'=>$tenants->current()]);
    }

    public function store(Request $request, TenantContext $tenants, AuditLogger $audit): RedirectResponse
    {
        $validated=$request->validate([
            'service_type'=>['required','in:accounting,audit_support,corporate_tax,personal_tax,vat,vcfo,cost_control,other'],
            'title'=>['required','string','max:190'],
            'start_date'=>['nullable','date'],
            'due_date'=>['nullable','date','after_or_equal:start_date'],
        ]);

        do {
            $code='ENG-'.now('Asia/Dhaka')->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Engagement::where('organization_id',$tenants->id())->where('code',$code)->exists());

        $engagement=Engagement::create([
            'organization_id'=>$tenants->id(),
            'code'=>$code,
            'service_type'=>$validated['service_type'],
            'title'=>$validated['title'],
            'stage'=>'requested',
            'status'=>'active',
            'start_date'=>$validated['start_date'] ?? null,
            'due_date'=>$validated['due_date'] ?? null,
            'lead_user_id'=>$request->user()->id,
        ]);

        $audit->record('engagement.created',$request->user(),$tenants->current(),$engagement,request:$request);

        return redirect()->route('workspace.engagements.show',$engagement);
    }

    public function show(Engagement $engagement, TenantContext $tenants): View
    {
        abort_unless($engagement->organization_id===$tenants->id(),404);

        return view('workspace.engagements.show', [
            'organization'=>$tenants->current(),
            'engagement'=>$engagement->load([
                'documents'=>fn($q)=>$q->where('status','active')->orderByDesc('id'),
            ]),
        ]);
    }

    public function advance(
        Request $request,
        Engagement $engagement,
        TenantContext $tenants,
        EngagementWorkflow $workflow,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($engagement->organization_id===$tenants->id(),404);

        $before=$engagement->stage->value;

        try {
            $workflow->advance($engagement);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['stage'=>$exception->getMessage()]);
        }

        $audit->record(
            'engagement.stage_advanced',
            $request->user(),
            $tenants->current(),
            $engagement,
            ['from'=>$before,'to'=>$engagement->stage->value],
            $request,
        );

        return redirect()->route('workspace.engagements.show',$engagement);
    }
}
