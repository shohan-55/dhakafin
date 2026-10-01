@extends('layouts.workspace')
@section('title','Workspace — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin">
<div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div>
<div style="display:flex;gap:10px;align-items:center">
@if($organizations->count() > 1)
<details style="position:relative"><summary class="btn ghost" style="cursor:pointer;list-style:none">Switch company</summary>
<div style="position:absolute;right:0;top:52px;background:white;color:#102421;border:1px solid #dcebe7;border-radius:14px;padding:8px;width:230px;box-shadow:0 18px 50px rgba(7,59,53,.15);z-index:30">
@foreach($organizations as $org)
<form method="post" action="{{ route('workspace.switch',$org) }}">@csrf<button type="submit" style="width:100%;text-align:left;border:0;background:transparent;padding:10px;border-radius:9px;font:inherit;cursor:pointer">{{ $org->name }}</button></form>
@endforeach
</div></details>
@endif
<form method="post" action="{{ route('logout') }}">@csrf<button class="btn ghost" type="submit">Sign out</button></form>
</div></div></nav>
<main class="wsmain">
<div class="eyebrow">SaaS workspace</div><h2 style="margin-bottom:10px">{{ $organization->name }}</h2><p class="muted">Accounting, tax, VAT, compliance and professional-service workflows converge here.</p>
<div class="wsgrid" style="margin-top:28px">
<div class="wscard"><h3>Compliance obligations</h3><p class="muted">{{ $openObligations->count() }} upcoming/open items loaded for this organization.</p></div>
@if(auth()->user()->hasPermission('tax.view',$organization))
<div class="wscard"><h3>TDS / VDS</h3><p class="muted">Track calculation, counterparty and challan evidence by company.</p><a href="{{ route('workspace.withholding.index') }}"><strong>Open TDS / VDS →</strong></a></div>
@else
<div class="wscard"><h3>TDS / VDS</h3><p class="muted">Your current role does not include tax workspace access.</p></div>
@endif
<div class="wscard"><h3>Security</h3><p class="muted"><a href="{{ route('mfa.setup') }}">Configure MFA</a> and protect sensitive finance access.</p></div>
</div>
@if(auth()->user()->hasPermission('audit-log.view',$organization))
<div class="wscard" style="margin-top:16px"><h3>Audit trail</h3><p class="muted">Review sensitive activity for this organization.</p><a href="{{ route('workspace.audit-log') }}"><strong>Open audit log →</strong></a></div>
@endif
@if($openObligations->isNotEmpty())<div class="wscard" style="margin-top:16px"><h3>Upcoming obligations</h3>@foreach($openObligations as $item)<p><strong>{{ $item->title }}</strong> · {{ $item->due_date->format('d M Y') }}</p>@endforeach</div>@endif
</main>
@endsection
