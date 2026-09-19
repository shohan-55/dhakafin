@extends('layouts.app', ['bodyClass' => 'page-inner'])

@section('content')

<section class="inner-hero">
    <canvas class="hero-canvas hero-canvas--inner" id="heroCanvas" aria-hidden="true"></canvas>
    <div class="container">
        <p class="hero-eyebrow" data-reveal><span class="pulse-dot"></span> Pricing — published, not negotiated in the dark</p>
        <h1 class="inner-title" data-reveal-lines>
            <span class="headline-line"><span>Know the cost before</span></span>
            <span class="headline-line"><span><em>you shake our hand.</em></span></span>
        </h1>
        <p class="inner-sub" data-reveal>Every engagement is a fixed scope at a fixed price, proposed within 48 hours of a free discovery call. Ranges below are honest market benchmarks — your quote depends on volume and complexity.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="price-grid" data-reveal-group>
            @foreach($packages as $pkg)
            <div class="price-card {{ !empty($pkg['featured']) ? 'price-card--featured' : '' }}" data-tilt>
                @if(!empty($pkg['featured']))<span class="price-badge">{{ $pkg['tag'] }}</span>@endif
                <h3>{{ $pkg['name'] }}</h3>
                <div class="price-num"><strong>{{ $pkg['price'] }}</strong><span>{{ $pkg['period'] }}</span></div>
                <ul>
                    @foreach($pkg['items'] as $item)
                    <li><svg class="icon" viewBox="0 0 24 24"><use href="#i-check"/></svg>{{ $item }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('contact') }}" class="btn {{ !empty($pkg['featured']) ? 'btn-solid' : 'btn-line' }}" data-magnetic>
                    <span>Start with {{ $pkg['name'] }}</span>
                    <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow" data-reveal>À-la-carte benchmarks</p>
            <h2 class="headline" data-reveal-lines>
                <span class="headline-line"><span>Per-service <em>price bands.</em></span></span>
            </h2>
        </div>
        <div class="price-table" data-reveal>
            @php
            $bands = [
                ['Personal income tax return', '৳3,000 – ৳25,000', '<strong>Tax</strong>'],
                ['TIN / BIN registration', '৳2,000 – ৳5,000', '<strong>Tax · VAT</strong>'],
                ['Monthly bookkeeping', '৳8,000 – ৳40,000 /mo', '<strong>Accounting</strong>'],
                ['VAT compliance retainer', '৳10,000 – ৳30,000 /mo', '<strong>VAT</strong>'],
                ['Corporate tax return', '৳25,000 – ৳1,50,000 /yr', '<strong>Tax</strong>'],
                ['Payroll processing', '৳500 – ৳1,000 /employee /mo', '<strong>Accounting</strong>'],
                ['Audit support (statutory pre-audit)', '৳30,000 – ৳2,00,000', '<strong>Audit</strong>'],
                ['CostGuard leak review', '৳75,000+ or 10–20% of recovery', '<strong>Cost efficiency</strong>'],
                ['Company incorporation', '৳25,000 – ৳60,000 + govt fees', '<strong>Corporate</strong>'],
                ['RJSC annual return & filings', '৳30,000 – ৳80,000 /yr', '<strong>Corporate</strong>'],
                ['Virtual CFO & MIS', '৳40,000 – ৳1,50,000 /mo', '<strong>Advisory</strong>'],
            ];
            @endphp
            @foreach($bands as $b)
            <div class="price-row">
                <span class="price-row-name">{!! $b[0] !!}</span>
                <span class="price-row-cat">{!! $b[2] !!}</span>
                <span class="price-row-band">{{ $b[1] }}</span>
            </div>
            @endforeach
        </div>
        <p class="fineprint" data-reveal>Rates are benchmarks against the Dhaka SME market and current Finance Act; a signed proposal fixes the exact figure before work begins. Government fees &amp; stamp duties are passed through at cost.</p>
    </div>
</section>

@endsection
