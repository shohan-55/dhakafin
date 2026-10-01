@extends('layouts.workspace')
@section('title','MFA verification — DhakaFin')
@section('body')
<div class="authwrap"><form class="authcard" method="post" action="{{ route('mfa.challenge.store') }}">@csrf
<div class="brand"><span class="mark">DF</span><span>DhakaFin</span></div>
<h2>Two-factor verification</h2><p class="muted">Enter the 6-digit authenticator code or one unused recovery code.</p>
<div class="field"><label>Authentication code</label><input name="code" autocomplete="one-time-code" autofocus required>@error('code')<div class="error">{{ $message }}</div>@enderror</div>
<button class="btn primary" style="width:100%;border:0" type="submit">Verify</button>
</form></div>
@endsection
