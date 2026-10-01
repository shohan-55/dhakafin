@extends('layouts.workspace')
@section('title','New TDS / VDS transaction — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.withholding.index') }}">Back</a></div></nav>
<main class="wsmain"><div style="max-width:760px">
<div class="eyebrow">Tax & VAT workspace</div><h2>New TDS / VDS transaction</h2>
<form class="wscard" method="post" action="{{ route('workspace.withholding.store') }}">@csrf
<div class="field"><label>Type</label><select name="kind"><option value="tds">TDS</option><option value="vds">VDS</option></select></div>
<div class="field"><label>Transaction date</label><input type="date" name="transaction_date" value="{{ old('transaction_date',date('Y-m-d')) }}" required>@error('transaction_date')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Reference</label><input name="reference" value="{{ old('reference') }}"></div>
<div class="field"><label>Counterparty name</label><input name="counterparty_name" value="{{ old('counterparty_name') }}"></div>
<div class="field"><label>Counterparty TIN</label><input name="counterparty_tin" value="{{ old('counterparty_tin') }}"></div>
<div class="field"><label>Base amount (BDT)</label><input name="amount" inputmode="decimal" value="{{ old('amount') }}" required>@error('amount')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Applicable rate (%)</label><input name="rate" inputmode="decimal" value="{{ old('rate') }}" required>@error('rate')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Challan reference (optional)</label><input name="challan_reference" value="{{ old('challan_reference') }}"></div>
<div class="field"><label>Challan date (optional)</label><input type="date" name="challan_date" value="{{ old('challan_date') }}"></div>
<button class="btn primary" style="border:0" type="submit">Save transaction</button>
</form>
</div></main>
@endsection
