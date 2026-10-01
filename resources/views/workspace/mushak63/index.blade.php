@extends('layouts.workspace')
@section('title','Mushak 6.3 — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div style="display:flex;align-items:end;justify-content:space-between;gap:18px;flex-wrap:wrap">
<div><div class="eyebrow">VAT workspace</div><h2 style="margin-bottom:8px">Mushak 6.3 drafts</h2><p class="muted">Draft records use explicit line values and VAT amounts; statutory layout/finalization is kept separate from data entry.</p></div>
<a class="btn primary" href="{{ route('workspace.mushak63.create') }}">New draft</a>
</div>
@if(session('status'))<div class="wscard" style="margin:20px 0">{{ session('status') }}</div>@endif
<div class="wscard" style="margin-top:24px;overflow:auto">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="text-align:left"><th style="padding:10px">Date</th><th style="padding:10px">Serial</th><th style="padding:10px">Buyer</th><th style="padding:10px">Value</th><th style="padding:10px">VAT</th><th style="padding:10px">Status</th></tr></thead>
<tbody>
@forelse($forms as $form)
<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $form->issue_date?->format('d M Y') }}</td><td style="padding:10px">{{ $form->serial_no ?: '—' }}</td><td style="padding:10px">{{ $form->buyer_name ?: '—' }}</td><td style="padding:10px">৳ {{ $form->totalValueFormatted() }}</td><td style="padding:10px">৳ {{ $form->totalVatFormatted() }}</td><td style="padding:10px">{{ ucfirst($form->status) }}</td></tr>
@empty
<tr><td colspan="6" class="muted" style="padding:16px">No Mushak 6.3 draft saved yet.</td></tr>
@endforelse
</tbody></table></div>
</main>
@endsection
