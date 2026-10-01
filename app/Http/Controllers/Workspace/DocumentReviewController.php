<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentReview;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentReviewController extends Controller
{
    public function store(
        Request $request,
        Document $document,
        TenantContext $tenants,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($document->organization_id===$tenants->id(),404);
        abort_if($document->uploaded_by===$request->user()->id,403,'Uploader cannot review their own document.');

        $validated=$request->validate([
            'outcome'=>['required','in:approved,changes_requested,rejected'],
            'notes'=>['nullable','string','max:4000'],
        ]);

        DocumentReview::create([
            'document_id'=>$document->id,
            'reviewer_user_id'=>$request->user()->id,
            'outcome'=>$validated['outcome'],
            'notes'=>$validated['notes'] ?? null,
        ]);

        $document->forceFill([
            'review_status'=>$validated['outcome'],
            'reviewed_by'=>$request->user()->id,
            'reviewed_at'=>now(),
        ])->save();

        $audit->record('document.reviewed',$request->user(),$tenants->current(),$document,[
            'outcome'=>$validated['outcome'],
        ],$request);

        return redirect()->route('workspace.engagements.show',$document->engagement_id);
    }
}
