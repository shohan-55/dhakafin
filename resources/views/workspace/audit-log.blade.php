@extends('layouts.workspace')
@section('title','Audit log — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div class="eyebrow">Security & accountability</div><h2 style="margin-bottom:8px">Audit log</h2><p class="muted">Recent sensitive actions recorded for this organization.</p>
<div class="wscard" style="margin-top:24px;overflow:auto">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="text-align:left"><th style="padding:10px">When</th><th style="padding:10px">Action</th><th style="padding:10px">Actor</th><th style="padding:10px">IP</th></tr></thead>
<tbody>
@forelse($events as $event)
<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px;white-space:nowrap">{{ $event->occurred_at?->format('d M Y H:i') }}</td><td style="padding:10px">{{ $event->action }}</td><td style="padding:10px">{{ $event->actor?->name ?? 'System' }}</td><td style="padding:10px">{{ $event->ip_address ?? '—' }}</td></tr>
@empty
<tr><td colspan="4" class="muted" style="padding:16px">No audit events recorded yet.</td></tr>
@endforelse
</tbody></table>
</div></main>
@endsection
