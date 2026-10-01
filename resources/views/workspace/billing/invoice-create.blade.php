@extends('layouts.workspace')
@section('title','New invoice — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.billing.index') }}">Back</a></div></nav>
<main class="wsmain"><div style="max-width:920px"><div class="eyebrow">Billing</div><h2>New invoice draft</h2>
<form class="wscard" method="post" action="{{ route('workspace.billing.invoices.store') }}">@csrf
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><div class="field"><label>Issue date</label><input type="date" name="issue_date" value="{{ old('issue_date',date('Y-m-d')) }}" required></div><div class="field"><label>Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}"></div></div>
<h3>Lines</h3>@error('rows')<div class="error">{{ $message }}</div>@enderror
<div style="overflow:auto"><table style="width:100%;min-width:760px;border-collapse:collapse"><thead><tr><th style="padding:8px;text-align:left">Description</th><th style="padding:8px;text-align:left">Qty</th><th style="padding:8px;text-align:left">Unit BDT</th><th style="padding:8px;text-align:left">Tax BDT</th></tr></thead><tbody>
@for($i=0;$i<5;$i++)<tr><td style="padding:8px"><input name="rows[{{ $i }}][description]" value="{{ old("rows.$i.description") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][quantity]" value="{{ old("rows.$i.quantity",$i===0?'1':'') }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][unit_amount]" value="{{ old("rows.$i.unit_amount") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][tax_amount]" value="{{ old("rows.$i.tax_amount",'0') }}"></td></tr>@endfor
</tbody></table></div>
<div class="field"><label>Notes</label><input name="notes" value="{{ old('notes') }}"></div>
<button class="btn primary" style="border:0" type="submit">Save draft</button>
</form></div></main>
@endsection
