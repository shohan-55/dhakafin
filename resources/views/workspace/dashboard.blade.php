@extends('layouts.workspace')
@section('title','Workspace — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><form method="post" action="{{ route('logout') }}">@csrf<button class="btn ghost" type="submit">Sign out</button></form></div></nav>
<main class="wsmain">
<div class="eyebrow">SaaS workspace</div><h2 style="margin-bottom:10px">{{ $organization->name }}</h2><p class="muted">Accounting, tax, VAT, compliance and professional-service workflows will converge here.</p>
<div class="wsgrid" style="margin-top:28px">
<div class="wscard"><h3>Compliance obligations</h3><p class="muted">{{ $openObligations->count() }} upcoming/open items loaded for this organization.</p></div>
<div class="wscard"><h3>TDS / VDS</h3><p class="muted">Calculation engine is active; transaction workflow comes next.</p></div>
<div class="wscard"><h3>Security</h3><p class="muted"><a href="{{ route('mfa.setup') }}">Configure MFA</a> and protect sensitive finance access.</p></div>
</div>
@if($openObligations->isNotEmpty())<div class="wscard" style="margin-top:16px"><h3>Upcoming obligations</h3>@foreach($openObligations as $item)<p><strong>{{ $item->title }}</strong> · {{ $item->due_date->format('d M Y') }}</p>@endforeach</div>@endif
</main>
@endsection
