@extends('layouts.app', ['bodyClass' => 'page-inner'])

@section('content')

<section class="inner-hero">
    <canvas class="hero-canvas hero-canvas--inner" id="heroCanvas" aria-hidden="true"></canvas>
    <div class="container">
        <p class="hero-eyebrow" data-reveal><span class="pulse-dot"></span> About DhakaFin</p>
        <h1 class="inner-title" data-reveal-lines>
            <span class="headline-line"><span>One partner for compliance</span></span>
            <span class="headline-line"><span><em>&amp; cost control.</em></span></span>
        </h1>
        <p class="inner-sub" data-reveal>
            DhakaFin was built on a simple frustration: Bangladeshi SMEs deserve the same discipline
            that big corporates buy for crores — delivered at a price a growing business can actually pay.
        </p>
    </div>
</section>

<section class="section">
    <div class="container about-grid">
        <div class="about-block" data-reveal>
            <span class="about-num">01</span>
            <h2>The practice model</h2>
            <p>We keep every recurring function — books, VAT, payroll, RJSC, tax — on a monthly compliance calendar with a named account manager. Deadlines stop being surprises; they become routine.</p>
        </div>
        <div class="about-block" data-reveal>
            <span class="about-num">02</span>
            <h2>Independence, by design</h2>
            <p>Statutory audit sign-off runs through our network of ICAB-member chartered accountants, exactly as the profession demands. DhakaFin delivers the books, files and schedules — the CA signs, independently.</p>
        </div>
        <div class="about-block" data-reveal>
            <span class="about-num">03</span>
            <h2>We hunt leaks</h2>
            <p>Most firms stop at compliance. Our CostGuard reviews go further — procurement, vendors, expense lines and inventory — because a compliant business that bleeds money is still losing.</p>
        </div>
        <div class="about-block" data-reveal>
            <span class="about-num">04</span>
            <h2>Rooted in Dhaka</h2>
            <p>Motijheel to Gulshan to Narayanganj — we work in NBR circles, RJSC corridors and trade offices every week, in Bangla and English. Regulation here rewards those who show up. We show up.</p>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="values-strip" data-reveal-group>
            <div class="value-pill">No hidden fees</div>
            <div class="value-pill">Deadlines are sacred</div>
            <div class="value-pill">Plain-language reports</div>
            <div class="value-pill">Confidential, always</div>
            <div class="value-pill">Senior review on everything</div>
        </div>
    </div>
</section>

@endsection
