@extends('layouts.workspace')
@section('title','Sign in — DhakaFin')
@section('body')
<div class="authwrap"><form class="authcard" method="post" action="{{ route('login.store') }}">@csrf
<div class="brand"><span class="mark">DF</span><span>DhakaFin</span></div>
<h2 style="margin-bottom:8px">Sign in</h2><p class="muted">Access your organization workspace securely.</p>
<div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Password</label><input type="password" name="password" autocomplete="current-password" required></div>
<label style="display:flex;gap:9px;align-items:center;margin:16px 0"><input type="checkbox" name="remember" value="1"> Remember me</label>
<button class="btn primary" style="width:100%;border:0" type="submit">Continue</button>
<p class="muted" style="text-align:center;margin-top:18px">New to DhakaFin? <a href="{{ route('register') }}"><strong>Create a workspace</strong></a></p>
</form></div>
@endsection
