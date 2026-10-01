@extends('layouts.workspace')
@section('title','TDS / VDS — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div style="display:flex;align-items:end;justify-content:space-between;gap:18px;flex-wrap:wrap">
<div><div class="eyebrow">Tax & VAT workspace</div><h2 style="margin-bottom:8px">TDS / VDS transactions</h2><p class="muted">Store calculation, counterparty and challan evidence per organization.</p></div>
<a class="btn primary" href="{{ route('workspace.withholding.create') }}">New transaction</a>
</div>
@if(session('status'))<div class="wscard" style="margin:20px 0">{{ session('status') }}</div>@endif
<div class="wscard" style="margin-top:24px;overflow:auto">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="text-align:left"><th style="padding:10px">Date</th><th style="padding:10px">Type</th><th style="padding:10px">Counterparty</th><th style="padding:10px">Base</th><th style="padding:10px">Rate</th><th style="padding:10px">Withheld</th><th style="padding:10px">Status</th></tr></thead>
<tbody>
@forelse($transactions as $tx)
<tr style="border-top:1px solid #e2eeeb">
<td style="padding:10px;white-space:nowrap">{{ $tx->transaction_date->format('d M Y') }}</td>
<td style="padding:10px">{{ strtoupper($tx->kind) }}</td>
<td style="padding:10px">{{ $tx->counterparty_name ?: '—' }}</td>
<td style="padding:10px">৳ {{ number_format($tx->base_amount_minor/100,2) }}</td>
<td style="padding:10px">{{ rtrim(rtrim(number_format($tx->rate_ppm/10000,4,'.',''),'0'),'.') }}%</td>
<td style="padding:10px">৳ {{ number_format($tx->withheld_amount_minor/100,2) }}</td>
<td style="padding:10px">{{ ucfirst($tx->status) }}</td>
</tr>
@empty
<tr><td colspan="7" class="muted" style="padding:16px">No TDS/VDS transaction saved yet.</td></tr>
@endforelse
</tbody></table>
</div>
</main>
@endsection
