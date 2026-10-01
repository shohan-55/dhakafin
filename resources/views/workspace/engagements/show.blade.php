@extends('layouts.workspace')
@section('title',$engagement->title.' — DhakaFin')
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.engagements.index') }}">Engagements</a></div></nav>
<main class="wsmain">
<div style="display:flex;justify-content:space-between;gap:20px;align-items:end;flex-wrap:wrap">
<div><div class="eyebrow">{{ $engagement->code }} · {{ str($engagement->service_type)->replace('_',' ')->title() }}</div><h2 style="margin-bottom:8px">{{ $engagement->title }}</h2><p class="muted">Stage: <strong>{{ $engagement->stage->label() }}</strong> @if($engagement->due_date) · Due {{ $engagement->due_date->format('d M Y') }} @endif</p></div>
@if(auth()->user()->hasPermission('engagements.manage',$organization) && $engagement->status==='active')
<form method="post" action="{{ route('workspace.engagements.advance',$engagement) }}">@csrf @method('PATCH')<button class="btn primary" style="border:0" type="submit">Advance stage</button></form>
@endif
</div>

@if(auth()->user()->hasPermission('documents.manage',$organization))
<div class="wscard" style="margin-top:24px">
<h3>Upload private document</h3><form method="post" enctype="multipart/form-data" action="{{ route('workspace.engagements.documents.store',$engagement) }}">@csrf
<div class="field"><label>Document title</label><input name="title"></div>
<div class="field"><label>File</label><input type="file" name="document" required>@error('document')<div class="error">{{ $message }}</div>@enderror</div>
<button class="btn primary" style="border:0" type="submit">Upload securely</button>
</form></div>
@endif

<div class="wscard" style="margin-top:16px"><h3>Documents</h3>
<div style="display:grid;gap:12px">
@forelse($engagement->documents as $document)
<div style="border-top:1px solid #e2eeeb;padding-top:14px">
<div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap"><div><strong>{{ $document->title }}</strong><div class="muted">{{ $document->original_name }} · {{ number_format($document->size_bytes) }} bytes · Review: {{ str($document->review_status)->replace('_',' ')->title() }}</div></div>
@if(auth()->user()->hasPermission('documents.view',$organization))<a href="{{ route('workspace.documents.download',$document) }}"><strong>Download</strong></a>@endif
</div>
@if(auth()->user()->hasPermission('documents.review',$organization) && $document->uploaded_by !== auth()->id())
<form method="post" action="{{ route('workspace.documents.review',$document) }}" style="display:grid;grid-template-columns:180px 1fr auto;gap:10px;align-items:end;margin-top:12px">@csrf
<div class="field" style="margin:0"><label>Review</label><select name="outcome"><option value="approved">Approve</option><option value="changes_requested">Changes requested</option><option value="rejected">Reject</option></select></div>
<div class="field" style="margin:0"><label>Note</label><input name="notes"></div>
<button class="btn primary" style="border:0" type="submit">Submit review</button>
</form>
@endif
</div>
@empty
<p class="muted">No document uploaded yet.</p>
@endforelse
</div></div>
</main>
@endsection
