<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="DhakaFin verified Bangladesh tax and VAT compliance calendar.">
<title>Tax & VAT Calendar — DhakaFin</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<nav class="nav"><div class="shell navin"><a class="brand" href="{{ route('home') }}"><span class="mark">DF</span><span>DhakaFin</span></a><a class="btn ghost" href="{{ route('withholding-calculator') }}">TDS / VDS Calculator</a></div></nav>
<main class="section"><div class="shell">
<div class="eyebrow">Verified compliance intelligence</div><h1 style="font-size:clamp(2.7rem,7vw,4.8rem);margin-top:14px">Tax & VAT Calendar</h1>
<p class="lead">Only rules that have a recorded source and verification timestamp are published here. Effective dates remain part of each rule so updates do not overwrite history.</p>
<div class="cards" style="margin-top:36px">
@forelse($rules as $rule)
<article class="card">
<small>{{ strtoupper($rule->category) }} · {{ strtoupper($rule->frequency) }}</small>
<h3>{{ $rule->title }}</h3>
<p>Rule code: {{ $rule->code }}</p>
@if($rule->verified_at)<p>Verified: {{ $rule->verified_at->format('d M Y') }}</p>@endif
@if($rule->source_url)<p><a href="{{ $rule->source_url }}" rel="noopener noreferrer" target="_blank"><strong>Official/source reference →</strong></a></p>@endif
</article>
@empty
<article class="card" style="grid-column:1/-1"><h3>Verification dataset is being prepared.</h3><p>No statutory deadline is shown until its source, effective period and verification record are loaded into DhakaFin.</p></article>
@endforelse
</div></div></main>
</body></html>
