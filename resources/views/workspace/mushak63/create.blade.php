@extends('layouts.workspace')
@section('title','New Mushak 6.3 draft — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.mushak63.index') }}">Back</a></div></nav>
<main class="wsmain"><div style="max-width:900px">
<div class="eyebrow">VAT workspace</div><h2>New Mushak 6.3 draft</h2>
<p class="muted">Enter the values you have verified for the transaction. DhakaFin does not infer statutory VAT treatment in this draft screen.</p>
<form class="wscard" method="post" action="{{ route('workspace.mushak63.store') }}">@csrf
<div class="field"><label>Serial no.</label><input name="serial_no" value="{{ old('serial_no') }}"></div>
<div class="field"><label>Issue date</label><input type="date" name="issue_date" value="{{ old('issue_date',date('Y-m-d')) }}" required></div>
<div class="field"><label>Buyer name</label><input name="buyer_name" value="{{ old('buyer_name') }}"></div>
<div class="field"><label>Buyer BIN</label><input name="buyer_bin" value="{{ old('buyer_bin') }}"></div>
<div class="field"><label>Buyer address</label><input name="buyer_address" value="{{ old('buyer_address') }}"></div>
<h3 style="margin-top:28px">Lines</h3>
@error('rows')<div class="error">{{ $message }}</div>@enderror
<div style="overflow:auto"><table style="width:100%;min-width:720px;border-collapse:collapse"><thead><tr><th style="text-align:left;padding:8px">Description</th><th style="text-align:left;padding:8px">Qty</th><th style="text-align:left;padding:8px">Value (BDT)</th><th style="text-align:left;padding:8px">VAT (BDT)</th></tr></thead><tbody>
@for($i=0;$i<5;$i++)
<tr><td style="padding:8px"><input name="rows[{{ $i }}][description]" value="{{ old("rows.$i.description") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][quantity]" value="{{ old("rows.$i.quantity") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][value]" value="{{ old("rows.$i.value") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][vat]" value="{{ old("rows.$i.vat") }}"></td></tr>
@endfor
</tbody></table></div>
<button class="btn primary" style="border:0;margin-top:20px" type="submit">Save draft</button>
</form></div></main>
@endsection
