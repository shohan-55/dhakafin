@extends('layouts.workspace')
@section('title','Create account — DhakaFin')
@section('body')
<div class="authwrap"><form class="authcard" method="post" action="{{ route('register.store') }}">@csrf
<div class="brand"><span class="mark">DF</span><span>DhakaFin</span></div>
<h2 style="margin-bottom:8px">Create your workspace</h2>
<p class="muted">Start with one organization. You can add additional companies and team members later.</p>
<div class="field"><label>Your name</label><input name="name" value="{{ old('name') }}" required>@error('name')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Work email</label><input type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Organization name</label><input name="organization_name" value="{{ old('organization_name') }}" required>@error('organization_name')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Password</label><input type="password" name="password" autocomplete="new-password" required>@error('password')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Confirm password</label><input type="password" name="password_confirmation" autocomplete="new-password" required></div>
<button class="btn primary" style="width:100%;border:0" type="submit">Create DhakaFin workspace</button>
<p class="muted" style="text-align:center;margin-top:18px">Already have an account? <a href="{{ route('login') }}"><strong>Sign in</strong></a></p>
</form></div>
@endsection
