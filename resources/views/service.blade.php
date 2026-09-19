@extends('layouts.app', ['bodyClass' => 'page-inner'])

@section('content')

<section class="inner-hero">
    <canvas class="hero-canvas hero-canvas--inner" id="heroCanvas" aria-hidden="true"></canvas>
    <div class="container">
        <a href="{{ route('home') }}#services" class="crumb" data-reveal>
            <svg class="icon" viewBox="0 0 24 24" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>
            All services
        </a>
        <div class="inner-hero-head">
            <span class="service-idx service-idx--lg">
                <span>{{ str_pad($service['rank'], 2, '0', STR_PAD_LEFT) }}</span>
                @if($service['medal'])
                    <i class="medal medal--{{ $service['medal'] }}">{{ $service['medal'] === 'gold' ? '🥇' : ($service['medal'] === 'silver' ? '🥈' : '🥉') }}</i>
                @endif
            </span>
            <h1 class="inner-title" data-reveal-lines>
                @foreach(explode(' & ', $service['title']) as $k => $part)
                    <span class="headline-line"><span>@if($k > 0)&amp; @endif{{ $part }}</span></span>
                @endforeach
            </h1>
            @if(!empty($service['flagship']))
                <span class="flagship-chip" data-reveal>Includes the <strong>{{ $service['flagship'] }}</strong> review</span>
            @endif
        </div>
        <p class="inner-sub" data-reveal>{{ $service['description'] }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--split">
            <div>
                <p class="eyebrow" data-reveal>What's included</p>
                <h2 class="headline" data-reveal-lines>
                    <span class="headline-line"><span>Every corner,</span></span>
                    <span class="headline-line"><span><em>covered.</em></span></span>
                </h2>
            </div>
        </div>
        <div class="subservice-grid" data-reveal-group>
            @foreach($service['items'] as $i => $item)
            <div class="subservice-card" data-tilt>
                <span class="subservice-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <h3>{{ $item }}</h3>
            </div>
            @endforeach
        </div>

        <div class="inner-cta" data-reveal>
            <p>Fixed-scope proposal within <strong>48 hours</strong> of a free discovery call.</p>
            <a href="{{ route('contact') }}" class="btn btn-solid" data-magnetic>
                <span>Get my proposal</span>
                <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
            </a>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <p class="eyebrow" data-reveal>Keep exploring</p>
        <div class="other-services">
            @foreach($services as $otherSlug => $other)
                @if($otherSlug !== $slug)
                <a href="{{ route('services.show', $otherSlug) }}" class="other-service" data-reveal>
                    <span>{{ $other['title'] }}</span>
                    <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                </a>
                @endif
            @endforeach
        </div>
    </div>
</section>

@endsection
