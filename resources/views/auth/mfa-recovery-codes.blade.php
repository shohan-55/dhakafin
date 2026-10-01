@extends('layouts.workspace')
@section('title','Recovery codes — DhakaFin')
@section('body')
<div class="authwrap"><div class="authcard">
<h2>Save your recovery codes</h2><p class="muted">Each code can be used once. Store them somewhere secure; DhakaFin stores only protected hashes, not the original codes.</p>
<div class="codes">@foreach($codes as $code)<div class="code">{{ $code }}</div>@endforeach</div>
<a class="btn primary" style="width:100%;margin-top:22px" href="{{ route('workspace.dashboard') }}">Continue to workspace</a>
</div></div>
@endsection
