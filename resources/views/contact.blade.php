@extends('layouts.app', ['bodyClass' => 'page-inner', 'hideCta' => true])

@section('content')

<section class="inner-hero inner-hero--contact">
    <canvas class="hero-canvas hero-canvas--inner" id="heroCanvas" aria-hidden="true"></canvas>
    <div class="container">
        <p class="hero-eyebrow" data-reveal><span class="pulse-dot"></span> Free 15-minute discovery call</p>
        <h1 class="inner-title" data-reveal-lines>
            <span class="headline-line"><span>Tell us what's leaking.</span></span>
            <span class="headline-line"><span><em>We'll bring the plug.</em></span></span>
        </h1>
    </div>
</section>

<section class="section section--tight contact-section">
    <div class="container contact-grid">
        <div class="contact-info" data-reveal-group>
            <a class="contact-line" href="mailto:{{ config('dhakafin.email') }}">
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-mail"/></svg>
                <div><small>Email</small><span>{{ config('dhakafin.email') }}</span></div>
            </a>
            <a class="contact-line" href="https://wa.me/{{ config('dhakafin.whatsapp') }}" target="_blank" rel="noopener">
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-wa"/></svg>
                <div><small>WhatsApp</small><span>{{ config('dhakafin.phone') }}</span></div>
            </a>
            <div class="contact-line">
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-pin"/></svg>
                <div><small>Office</small><span>{{ config('dhakafin.location') }}</span></div>
            </div>
            <div class="contact-note">
                <strong>What happens next?</strong>
                <ol>
                    <li>We reply within one business day.</li>
                    <li>A 15-minute call maps your exact needs.</li>
                    <li>Fixed-scope proposal lands within 48 hours.</li>
                </ol>
            </div>
        </div>

        <form class="contact-form" method="POST" action="{{ route('contact.store') }}" data-reveal>
            @csrf
            @if(session('success'))
                <div class="flash flash--ok">
                    <svg class="icon" viewBox="0 0 24 24"><use href="#i-check"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="flash flash--err">Please fix the highlighted fields and send again.</div>
            @endif

            <div class="field">
                <input type="text" name="name" id="f-name" value="{{ old('name') }}" placeholder=" " required>
                <label for="f-name">Your name</label>
                @error('name')<small class="err">{{ $message }}</small>@enderror
            </div>
            <div class="field-row">
                <div class="field">
                    <input type="email" name="email" id="f-email" value="{{ old('email') }}" placeholder=" " required>
                    <label for="f-email">Email</label>
                    @error('email')<small class="err">{{ $message }}</small>@enderror
                </div>
                <div class="field">
                    <input type="tel" name="phone" id="f-phone" value="{{ old('phone') }}" placeholder=" ">
                    <label for="f-phone">Phone (optional)</label>
                    @error('phone')<small class="err">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="field">
                <select name="service" id="f-service">
                    <option value="">What do you need help with?</option>
                    @foreach($services as $slug => $s)
                        <option value="{{ $s['title'] }}" @selected(old('service') === $s['title'])>{{ $s['title'] }}</option>
                    @endforeach
                    <option value="Something else" @selected(old('service') === 'Something else')>Something else</option>
                </select>
                <label for="f-service" class="field-label--static">Service</label>
            </div>
            <div class="field">
                <textarea name="message" id="f-msg" rows="5" placeholder=" " required>{{ old('message') }}</textarea>
                <label for="f-msg">Tell us about your business &amp; the problem</label>
                @error('message')<small class="err">{{ $message }}</small>@enderror
            </div>
            <button type="submit" class="btn btn-solid btn-lg btn-block" data-magnetic>
                <span>Send message</span>
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
            </button>
            <p class="fineprint">Your information stays confidential — always.</p>
        </form>
    </div>
</section>

@endsection
