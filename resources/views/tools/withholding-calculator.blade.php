<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="DhakaFin TDS and VDS calculator for Bangladesh finance workflows.">
<title>TDS / VDS Calculator — DhakaFin</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<nav class="nav"><div class="shell navin"><a class="brand" href="{{ route('home') }}"><span class="mark">DF</span><span>DhakaFin</span></a><a class="btn ghost" href="{{ route('tax-calendar') }}">Tax Calendar</a></div></nav>
<main class="section"><div class="shell" style="max-width:900px">
<div class="eyebrow">Free finance tool</div><h1 style="font-size:clamp(2.7rem,7vw,4.6rem);margin-top:14px">TDS / VDS Calculator</h1>
<p class="lead">Calculate withholding from a known applicable rate using deterministic BDT/paisa arithmetic. This tool does not decide which statutory rate applies to your transaction.</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:34px">
<form class="card" method="post" action="{{ route('withholding-calculator.calculate') }}" style="min-height:auto">@csrf
<div class="field"><label>Type</label><select name="kind" style="width:100%;min-height:46px;border:1px solid #cfdeda;border-radius:12px;padding:0 13px"><option value="tds" @selected(old('kind')==='tds')>TDS</option><option value="vds" @selected(old('kind')==='vds')>VDS</option></select></div>
<div class="field"><label>Base amount (BDT)</label><input name="amount" inputmode="decimal" value="{{ old('amount') }}" placeholder="100000.00" required>@error('amount')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Applicable rate (%)</label><input name="rate" inputmode="decimal" value="{{ old('rate') }}" placeholder="10" required>@error('rate')<div class="error">{{ $message }}</div>@enderror</div>
<button class="btn primary" style="width:100%;border:0" type="submit">Calculate</button>
</form>
<div class="panel" style="min-height:330px">
<small>Calculation result</small>
@if($result)
<h3>{{ $result['kind'] }} withholding</h3>
<div class="mini-grid"><div class="mini"><strong>৳ {{ $result['base'] }}</strong><span>Base amount</span></div><div class="mini"><strong>{{ $result['rate'] }}%</strong><span>Applied rate</span></div><div class="mini"><strong>৳ {{ $result['withheld'] }}</strong><span>Withholding</span></div><div class="mini"><strong>৳ {{ $result['net'] }}</strong><span>Net amount</span></div></div>
@else
<h3>Enter a known rate.</h3><p style="color:#c8deda;line-height:1.7">DhakaFin will later connect this calculator to a verified, effective-dated rate library. Until then, the user supplies the applicable statutory rate.</p>
@endif
</div>
</div>
<div class="card" style="margin-top:18px;min-height:auto"><strong>Important:</strong><p class="muted">Tax and VAT rates can depend on transaction type, taxpayer status, source, thresholds and effective date. Treat this as an arithmetic tool, not a substitute for checking the applicable law/rule.</p></div>
</div></main>
</body></html>
