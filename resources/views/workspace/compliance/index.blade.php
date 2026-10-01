@extends('layouts.workspace')
@section('title','Compliance — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div class="eyebrow">Compliance operating system</div><h2 style="margin-bottom:8px">Tax & VAT obligations</h2>
<p class="muted">Deadlines are organization-scoped and can be traced back to a verified compliance rule when available.</p>
@if(session('status'))<div class="wscard" style="margin:20px 0">{{ session('status') }}</div>@endif
<div style="display:grid;gap:14px;margin-top:24px">
@forelse($obligations as $item)
<article class="wscard">
<div style="display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap">
<div><div class="eyebrow">{{ strtoupper($item->type) }} · {{ $item->period_key ?: 'ONE-OFF' }}</div><h3>{{ $item->title }}</h3><p class="muted">Due {{ $item->due_date->format('d M Y') }} @if($item->rule) · Rule {{ $item->rule->code }} @endif</p></div>
<div><span class="pill">{{ ucfirst($item->status) }}</span></div>
</div>
@if($item->status === 'open' && auth()->user()->hasPermission('compliance.manage',$organization))
<form method="post" action="{{ route('workspace.compliance.complete',$item) }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end;margin-top:16px">@csrf @method('PATCH')
<div class="field" style="margin:0"><label>Evidence / challan reference</label><input name="evidence_reference"></div>
<div class="field" style="margin:0"><label>Note</label><input name="notes"></div>
<button class="btn primary" style="border:0" type="submit">Mark complete</button>
</form>
@elseif($item->status === 'completed')
<p class="muted">Completed {{ $item->completed_at?->format('d M Y H:i') }} @if($item->evidence_reference) · Evidence: {{ $item->evidence_reference }} @endif</p>
@endif
</article>
@empty
<div class="wscard"><h3>No obligations generated yet.</h3><p class="muted">Only verified and published compliance rules will generate recurring deadlines.</p></div>
@endforelse
</div></main>
@endsection
