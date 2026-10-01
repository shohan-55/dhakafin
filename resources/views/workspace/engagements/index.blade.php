@extends('layouts.workspace')
@section('title','Engagements — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div style="display:flex;align-items:end;justify-content:space-between;gap:18px;flex-wrap:wrap">
<div><div class="eyebrow">Service delivery</div><h2 style="margin-bottom:8px">Engagements</h2><p class="muted">Track accounting, tax, VAT, audit-support and advisory work through a controlled 12-stage workflow.</p></div>
@if(auth()->user()->hasPermission('engagements.manage',$organization))<a class="btn primary" href="{{ route('workspace.engagements.create') }}">New engagement</a>@endif
</div>
<div style="display:grid;gap:14px;margin-top:24px">
@forelse($engagements as $engagement)
<a class="wscard" href="{{ route('workspace.engagements.show',$engagement) }}">
<div style="display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap"><div><div class="eyebrow">{{ $engagement->code }} · {{ str($engagement->service_type)->replace('_',' ')->title() }}</div><h3>{{ $engagement->title }}</h3><p class="muted">{{ $engagement->stage->label() }} @if($engagement->due_date) · Due {{ $engagement->due_date->format('d M Y') }} @endif</p></div><span class="pill">{{ ucfirst($engagement->status) }}</span></div>
</a>
@empty
<div class="wscard"><h3>No engagement yet.</h3><p class="muted">Create the first service engagement for this organization.</p></div>
@endforelse
</div></main>
@endsection
