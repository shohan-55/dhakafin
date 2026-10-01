@extends('layouts.workspace')
@section('title','Enable MFA — DhakaFin')
@section('body')
<div class="authwrap"><form class="authcard" method="post" action="{{ route('mfa.setup.store') }}">@csrf
<h2>Enable two-factor authentication</h2>
<p class="muted">Add this secret to your authenticator app. QR rendering will be added to the settings UI; the standard otpauth URI is provided now for compatibility.</p>
<div class="field"><label>Secret</label><div class="codebox">{{ $secret }}</div></div>
<details><summary>Authenticator URI</summary><div class="codebox" style="margin-top:10px">{{ $uri }}</div></details>
<div class="field"><label>6-digit code</label><input name="code" inputmode="numeric" autocomplete="one-time-code" required>@error('code')<div class="error">{{ $message }}</div>@enderror</div>
<button class="btn primary" style="width:100%;border:0" type="submit">Verify and enable MFA</button>
</form></div>
@endsection
