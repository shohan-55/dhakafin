<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Engagement;
use App\Support\Auditing\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(
        Request $request,
        Engagement $engagement,
        TenantContext $tenants,
        AuditLogger $audit,
    ): RedirectResponse {
        abort_unless($engagement->organization_id===$tenants->id(),404);

        $validated=$request->validate([
            'title'=>['nullable','string','max:190'],
            'document'=>['required','file','max:20480','mimes:pdf,xlsx,xls,csv,doc,docx,jpg,jpeg,png,zip'],
        ]);

        $file=$validated['document'];
        $checksum=hash_file('sha256',$file->getRealPath());
        $extension=strtolower($file->getClientOriginalExtension());
        $filename=(string) Str::uuid().($extension ? '.'.$extension : '');
        $directory='organizations/'.$tenants->id().'/documents/'.now('Asia/Dhaka')->format('Y/m');
        $path=$file->storeAs($directory,$filename,'local');

        $document=Document::create([
            'organization_id'=>$tenants->id(),
            'engagement_id'=>$engagement->id,
            'uploaded_by'=>$request->user()->id,
            'title'=>$validated['title'] ?: $file->getClientOriginalName(),
            'original_name'=>$file->getClientOriginalName(),
            'disk'=>'local',
            'path'=>$path,
            'mime_type'=>$file->getMimeType(),
            'size_bytes'=>$file->getSize(),
            'checksum_sha256'=>$checksum,
            'status'=>'active',
            'sensitivity'=>'confidential',
            'review_status'=>'pending',
        ]);

        $audit->record('document.uploaded',$request->user(),$tenants->current(),$document,[
            'engagement_id'=>$engagement->id,
            'checksum_sha256'=>$checksum,
        ],$request);

        return redirect()->route('workspace.engagements.show',$engagement);
    }

    public function download(
        Request $request,
        Document $document,
        TenantContext $tenants,
        AuditLogger $audit,
    ): StreamedResponse {
        abort_unless($document->organization_id===$tenants->id(),404);
        abort_unless($document->status==='active',404);
        abort_unless(Storage::disk($document->disk)->exists($document->path),404);

        $audit->record('document.downloaded',$request->user(),$tenants->current(),$document,request:$request);

        return Storage::disk($document->disk)->download($document->path,$document->original_name);
    }
}
