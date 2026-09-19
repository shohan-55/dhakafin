@extends('layouts.app')

@section('content')

<!-- ==================== HERO ==================== -->
<section class="hero" id="hero">
    <canvas class="hero-canvas" id="heroCanvas" aria-hidden="true"></canvas>
    <div class="hero-orb hero-orb--a" aria-hidden="true"></div>
    <div class="hero-orb hero-orb--b" aria-hidden="true"></div>

    <div class="container hero-inner">
        <p class="hero-eyebrow" id="heroEyebrow">
            <span class="pulse-dot" aria-hidden="true"></span>
            Dhaka · Bangladesh — Finance, Compliance &amp; Advisory
        </p>

        <h1 class="hero-title" aria-label="Compliant. Leak-free. In control.">
            <span class="hero-line"><span class="hero-line-inner">Compliant.</span></span>
            <span class="hero-line"><span class="hero-line-inner hero-line--grad">Leak&#8209;free.</span></span>
            <span class="hero-line"><span class="hero-line-inner hero-line--outline">In&nbsp;control.</span></span>
        </h1>

        <p class="hero-sub" id="heroSub">
            Accounting, audit, tax, VAT &amp; cost&#8209;efficiency for Bangladesh's
            ambitious individuals, startups and SMEs — delivered by one obsessive partner.
        </p>

        <div class="hero-actions" id="heroActions">
            <a href="{{ route('contact') }}" class="btn btn-solid" data-magnetic>
                <span>Book a free consult</span>
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
            </a>
            <a href="#services" class="btn btn-ghost" data-magnetic data-scroll>
                <span>Explore services</span>
            </a>
        </div>

        <div class="hero-stats" id="heroStats">
            @foreach($stats as $stat)
            <div class="hero-stat">
                <div class="hero-stat-num">
                    <span data-count="{{ $stat['value'] }}">0</span><em>{{ $stat['suffix'] }}</em>
                </div>
                <div class="hero-stat-label">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="hero-side" aria-hidden="true"><span>Scroll to explore — Dhaka, Bangladesh</span></div>
    <a href="#services" class="hero-scroll" data-scroll aria-label="Scroll down">
        <span class="hero-scroll-track"><span class="hero-scroll-thumb"></span></span>
    </a>
</section>

<!-- ==================== TICKER ==================== -->
<div class="ticker" aria-hidden="true">
    <div class="ticker-row">
        <div class="ticker-track">
            @for($i = 0; $i < 2; $i++)
                <span>Tax Returns</span><i>◆</i><span>VAT Compliance</span><i>◆</i><span>Statutory Audit</span><i>◆</i><span>Bookkeeping</span><i>◆</i><span>Cost Leakage</span><i>◆</i><span>RJSC Filings</span><i>◆</i><span>Advisory</span><i>◆</i>
            @endfor
        </div>
    </div>
    <div class="ticker-row ticker-row--rev">
        <div class="ticker-track">
            @for($i = 0; $i < 2; $i++)
                <span>Compliant</span><i>◆</i><span>Leak-free</span><i>◆</i><span>In control</span><i>◆</i><span>কমপ্লায়েন্ট</span><i>◆</i><span>লিক-ফ্রি</span><i>◆</i><span>নিয়ন্ত্রণে</span><i>◆</i>
            @endfor
        </div>
    </div>
</div>

<!-- ==================== INTRO ==================== -->
<section class="section intro">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow" data-reveal>The problem we exist to solve</p>
            <h2 class="headline" data-reveal-lines>
                <span class="headline-line"><span>Most firms file your taxes.</span></span>
                <span class="headline-line"><span><em>We guard your money.</em></span></span>
            </h2>
        </div>
        <div class="intro-body" data-reveal>
            <p>
                SMEs in Bangladesh fight five battles with five different vendors — an accountant for books,
                an agent for VAT, a lawyer for tax, a CA firm once a year for audit, and nobody at all watching
                where the money leaks. <strong>DhakaFin is one partner for all of it</strong> — seven practice areas
                under one roof, one standard of care, one named team that answers the phone.
            </p>
            <a href="{{ route('about') }}" class="btn btn-line" data-magnetic>
                <span>Our story</span>
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- ==================== SERVICES ==================== -->
<section class="section services" id="services">
    <div class="container">
        <div class="section-head section-head--split">
            <div>
                <p class="eyebrow" data-reveal>What we do</p>
                <h2 class="headline" data-reveal-lines>
                    <span class="headline-line"><span>Seven disciplines.</span></span>
                    <span class="headline-line"><span><em>One standard: flawless.</em></span></span>
                </h2>
            </div>
            <p class="section-note" data-reveal>Medals are ranked by how much they move the needle for a growing business. Hover a row — everything we cover hides inside.</p>
        </div>

        <div class="service-list">
            @php
                $order = collect($services)->sortBy('rank');
                $icons = ['accounting-bookkeeping' => 'i-ledger', 'audit-assurance' => 'i-shield', 'tax-services' => 'i-percent', 'vat-services' => 'i-doc', 'cost-efficiency' => 'i-radar', 'corporate-compliance' => 'i-building', 'financial-advisory' => 'i-chart'];
            @endphp
            @foreach($order as $slug => $service)
            <a href="{{ route('services.show', $slug) }}" class="service-row" data-reveal>
                <div class="service-row-top">
                    <div class="service-idx">
                        <span>{{ str_pad($service['rank'], 2, '0', STR_PAD_LEFT) }}</span>
                        @if($service['medal'])
                            <i class="medal medal--{{ $service['medal'] }}" title="Core service">
                                {{ $service['medal'] === 'gold' ? '🥇' : ($service['medal'] === 'silver' ? '🥈' : '🥉') }}
                            </i>
                        @endif
                    </div>
                    <div class="service-name">
                        <svg class="service-ic" viewBox="0 0 24 24"><use href="#{{ $icons[$slug] }}"/></svg>
                        <h3>
                            {{ $service['title'] }}
                            @if(!empty($service['flagship']))<em class="flag">{{ $service['flagship'] }}</em>@endif
                        </h3>
                        <p>{{ $service['short'] }}</p>
                    </div>
                    <div class="service-arrow">
                        <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                    </div>
                </div>
                <div class="service-row-tags">
                    @foreach($service['items'] as $item)
                        <span>{{ $item }}</span>
                    @endforeach
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- ==================== COSTGUARD ==================== -->
<section class="section costguard" id="costguard">
    <div class="container">
        <div class="cg-panel">
            <div class="cg-scan" aria-hidden="true"></div>
            <div class="cg-copy">
                <p class="eyebrow eyebrow--gold" data-reveal>Flagship — the CostGuard review</p>
                <h2 class="headline" data-reveal-lines>
                    <span class="headline-line"><span>Is money quietly</span></span>
                    <span class="headline-line"><span><em>leaving your business?</em></span></span>
                </h2>
                <p class="cg-text" data-reveal>
                    Inflated purchase prices. Ghost vendors. Duplicate invoices. Inventory that evaporates.
                    Our <strong>CostGuard</strong> review x-rays your procurement, vendors, expenses and stock —
                    then hands you a quantified leak report and the controls to seal it.
                </p>
                <ul class="cg-points" data-reveal-group>
                    <li><svg class="icon" viewBox="0 0 24 24"><use href="#i-check"/></svg> Procurement &amp; purchase-cycle review</li>
                    <li><svg class="icon" viewBox="0 0 24 24"><use href="#i-check"/></svg> Vendor &amp; price benchmarking</li>
                    <li><svg class="icon" viewBox="0 0 24 24"><use href="#i-check"/></svg> Fraud-risk &amp; internal-control design</li>
                </ul>
                <div class="cg-actions" data-reveal>
                    <a href="{{ route('services.show', 'cost-efficiency') }}" class="btn btn-solid" data-magnetic>
                        <span>See CostGuard</span>
                        <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                    </a>
                    <p class="cg-fee">Fixed fee — or <strong>10–20% of what we recover</strong>. Your call.</p>
                </div>
            </div>
            <div class="cg-visual" aria-hidden="true">
                <div class="cg-rings">
                    <span class="cg-ring cg-ring--1"></span>
                    <span class="cg-ring cg-ring--2"></span>
                    <span class="cg-ring cg-ring--3"></span>
                    <span class="cg-core">
                        <svg viewBox="0 0 24 24" width="42" height="42"><use href="#i-radar"/></svg>
                    </span>
                    <span class="cg-blip cg-blip--1"></span>
                    <span class="cg-blip cg-blip--2"></span>
                    <span class="cg-blip cg-blip--3"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== PROCESS ==================== -->
<section class="section process">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow" data-reveal>How it works</p>
            <h2 class="headline" data-reveal-lines>
                <span class="headline-line"><span>From first call to</span></span>
                <span class="headline-line"><span><em>full control — in four moves.</em></span></span>
            </h2>
        </div>
        <div class="process-track" id="processTrack">
            <div class="process-progress" aria-hidden="true"><span id="processBar"></span></div>
            @foreach($process as $step)
            <div class="process-step" data-reveal>
                <span class="process-num">{{ $step['step'] }}</span>
                <h3>{{ $step['title'] }}</h3>
                <p>{{ $step['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ==================== WHY ==================== -->
<section class="section why">
    <div class="container">
        <div class="section-head section-head--split">
            <div>
                <p class="eyebrow" data-reveal>Why DhakaFin</p>
                <h2 class="headline" data-reveal-lines>
                    <span class="headline-line"><span>Built differently,</span></span>
                    <span class="headline-line"><span><em>on purpose.</em></span></span>
                </h2>
            </div>
            <p class="section-note" data-reveal>Anyone can file a return. Keeping a company airtight for twelve straight months is a different discipline entirely.</p>
        </div>
        <div class="why-grid" data-reveal-group>
            @foreach($why as $i => $w)
            <div class="why-card" data-tilt>
                <span class="why-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <h3>{{ $w['title'] }}</h3>
                <p>{{ $w['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ==================== TESTIMONIALS ==================== -->
<section class="section quotes">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow" data-reveal>Client words</p>
        </div>
        <div class="quotes-grid" data-reveal-group>
            @foreach($testimonials as $t)
            <figure class="quote-card">
                <div class="quote-mark" aria-hidden="true">”</div>
                <blockquote>{{ $t['quote'] }}</blockquote>
                <figcaption>
                    <strong>{{ $t['name'] }}</strong>
                    <span>{{ $t['meta'] }}</span>
                </figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>

<!-- ==================== PRICING PREVIEW ==================== -->
<section class="section pricing-preview">
    <div class="container">
        <div class="section-head section-head--split">
            <div>
                <p class="eyebrow" data-reveal>Pricing</p>
                <h2 class="headline" data-reveal-lines>
                    <span class="headline-line"><span>Real numbers,</span></span>
                    <span class="headline-line"><span><em>on the website.</em></span></span>
                </h2>
            </div>
            <p class="section-note" data-reveal>No "call for a quote" games. Fixed-scope proposals within 48 hours of a discovery call.</p>
        </div>
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
        <p class="pricing-more" data-reveal>Need à-la-carte prices? <a href="{{ route('pricing') }}">See the full price list →</a></p>
    </div>
</section>

@endsection
