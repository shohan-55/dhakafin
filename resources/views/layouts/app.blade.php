<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? 'DhakaFin — Compliant. Leak-free. In control.' }}</title>
    <meta name="description" content="{{ $meta['description'] ?? 'Accounting, audit, tax, VAT and cost-efficiency for ambitious Bangladeshi businesses.' }}">
    <meta property="og:title" content="{{ $meta['title'] ?? 'DhakaFin' }}">
    <meta property="og:description" content="{{ $meta['description'] ?? '' }}">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#04070d">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M4 28V12l12-8 12 8v16z' fill='none' stroke='%232DE3A7' stroke-width='2'/%3E%3Cpath d='M11 24v-6M16 24V14M21 24v-9' stroke='%23E7C164' stroke-width='2.4' stroke-linecap='round'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // failsafe: never trap visitors inside the preloader
        window.__dfBooted = false;
        setTimeout(function () {
            if (!window.__dfBooted) {
                document.documentElement.classList.add('no-motion');
                var p = document.getElementById('preloader');
                if (p) p.parentNode.removeChild(p);
                var c = document.getElementById('heroCanvas');
                if (c) c.classList.add('is-live');
            }
        }, 5000);
    </script>
</head>
<body class="{{ $bodyClass ?? '' }}">

    <!-- ====== PRELOADER ====== -->
    <div class="preloader" id="preloader" aria-hidden="true">
        <div class="preloader-inner">
            <div class="preloader-mark">
                <svg viewBox="0 0 64 64" width="72" height="72" aria-hidden="true">
                    <path d="M8 56V24L32 8l24 16v32" fill="none" stroke="url(#preg1)" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M22 48V36M32 48V28M42 48v-12" stroke="#E7C164" stroke-width="3" stroke-linecap="round"/>
                    <defs><linearGradient id="preg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2ed98f"/><stop offset="1" stop-color="#8bf3cb"/></linearGradient></defs>
                </svg>
            </div>
            <div class="preloader-word" aria-hidden="true">
                <span>D</span><span>H</span><span>A</span><span>K</span><span>A</span><span>F</span><span>I</span><span>N</span>
            </div>
            <div class="preloader-count" id="preloaderCount">00</div>
        </div>
        <div class="preloader-curtain preloader-curtain--a"></div>
        <div class="preloader-curtain preloader-curtain--b"></div>
    </div>

    <!-- ====== CURSOR ====== -->
    <div class="cursor-dot" id="cursorDot" aria-hidden="true"></div>
    <div class="cursor-ring" id="cursorRing" aria-hidden="true"><span class="cursor-label" id="cursorLabel"></span></div>

    <!-- ====== ATMOSPHERE ====== -->
    <div class="noise" aria-hidden="true"></div>
    <div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>

    <!-- ====== HEADER ====== -->
    <header class="site-header" id="siteHeader">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="logo" data-magnetic aria-label="DhakaFin home">
                <span class="logo-mark">
                    <svg viewBox="0 0 64 64" aria-hidden="true">
                        <rect x="3" y="3" width="58" height="58" rx="16" fill="none" stroke="url(#lg1)" stroke-width="2.5"/>
                        <path d="M18 44V34M28 44V26M38 44V30M48 44V20" stroke="url(#lg2)" stroke-width="3.4" stroke-linecap="round"/>
                        <circle cx="48" cy="16" r="3.5" fill="#8bf3cb"/>
                        <defs>
                            <linearGradient id="lg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2ed98f"/><stop offset="1" stop-color="#E7C164"/></linearGradient>
                            <linearGradient id="lg2" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#2ed98f"/><stop offset="1" stop-color="#E7C164"/></linearGradient>
                        </defs>
                    </svg>
                </span>
                <span class="logo-word">Dhaka<em>Fin</em></span>
            </a>

            <nav class="main-nav" aria-label="Primary">
                <a href="{{ route('home') }}#services" class="nav-link" data-magnetic>Services</a>
                <a href="{{ route('home') }}#costguard" class="nav-link nav-link--flag" data-magnetic>CostGuard</a>
                <a href="{{ route('pricing') }}" class="nav-link" data-magnetic>Pricing</a>
                <a href="{{ route('about') }}" class="nav-link" data-magnetic>About</a>
                <a href="{{ route('contact') }}" class="nav-link" data-magnetic>Contact</a>
            </nav>

            <div class="header-side">
                <a href="{{ route('contact') }}" class="btn btn-solid btn-sm" data-magnetic>
                    <span>Book a free consult</span>
                    <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                </a>
                <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ====== MOBILE MENU ====== -->
    <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
        <div class="mobile-menu-bg"></div>
        <nav class="mobile-nav" aria-label="Mobile">
            <a href="{{ route('home') }}" class="mobile-nav-link"><i>01</i><span>Home</span></a>
            <a href="{{ route('home') }}#services" class="mobile-nav-link"><i>02</i><span>Services</span></a>
            <a href="{{ route('home') }}#costguard" class="mobile-nav-link"><i>03</i><span>CostGuard</span></a>
            <a href="{{ route('pricing') }}" class="mobile-nav-link"><i>04</i><span>Pricing</span></a>
            <a href="{{ route('about') }}" class="mobile-nav-link"><i>05</i><span>About</span></a>
            <a href="{{ route('contact') }}" class="mobile-nav-link"><i>06</i><span>Contact</span></a>
        </nav>
        <div class="mobile-menu-foot">
            <a href="mailto:{{ config('dhakafin.email') }}">{{ config('dhakafin.email') }}</a>
            <span>{{ config('dhakafin.location') }}</span>
        </div>
    </div>

    <main id="main">
        @yield('content')
    </main>

    <!-- ====== CTA BAND ====== -->
    @unless(isset($hideCta) && $hideCta)
    <section class="cta-band">
        <div class="cta-glow" aria-hidden="true"></div>
        <div class="container">
            <p class="cta-kicker" data-reveal>Free 15-minute discovery call — no obligations</p>
            <h2 class="cta-title" data-reveal>
                Stop guessing.<br><em>Start controlling.</em>
            </h2>
            <div class="cta-actions" data-reveal>
                <a href="{{ route('contact') }}" class="btn btn-solid btn-lg" data-magnetic>
                    <span>Talk to DhakaFin</span>
                    <svg class="icon" viewBox="0 0 24 24"><use href="#i-arrow"/></svg>
                </a>
            </div>
        </div>
    </section>
    @endunless

    <!-- ====== FOOTER ====== -->
    <footer class="site-footer">
        <div class="container">
            <a href="{{ route('home') }}" class="footer-word" aria-label="DhakaFin">
                <span>D</span><span>H</span><span>A</span><span>K</span><span>A</span><span>F</span><span>I</span><span>N</span>
            </a>
            <div class="footer-grid">
                <div class="footer-col footer-col--brand">
                    <p class="footer-tag">{{ config('dhakafin.tagline') }}</p>
                    <p class="footer-desc">Professional accounting, audit, tax, VAT &amp; financial advisory for individuals, startups and SMEs in Bangladesh.</p>
                </div>
                <div class="footer-col">
                    <h4>Services</h4>
                    @foreach(config('dhakafin.services') as $slug => $service)
                        <a href="{{ route('services.show', $slug) }}">{{ $service['title'] }}</a>
                    @endforeach
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <a href="{{ route('about') }}">About</a>
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <a href="{{ route('contact') }}">Contact</a>
                </div>
                <div class="footer-col">
                    <h4>Reach us</h4>
                    <a href="mailto:{{ config('dhakafin.email') }}">{{ config('dhakafin.email') }}</a>
                    <a href="tel:{{ preg_replace('/\s+/', '', config('dhakafin.phone')) }}">{{ config('dhakafin.phone') }}</a>
                    <span class="footer-loc">{{ config('dhakafin.location') }}</span>
                </div>
            </div>
            <div class="footer-bar">
                <span>© {{ date('Y') }} DhakaFin — dhakafin.com</span>
                <span>Made in Dhaka, Bangladesh</span>
            </div>
        </div>
    </footer>

    <!-- ====== ICON SPRITE ====== -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14m0 0-6-6m6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
            <symbol id="i-check" viewBox="0 0 24 24"><path d="m4 12.5 5 5L20 6.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></symbol>
            <symbol id="i-ledger" viewBox="0 0 24 24"><path d="M5 3h11a2 2 0 0 1 2 2v16H7a2 2 0 0 1-2-2V3z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 8h5M9 12h5M9 16h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
            <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
            <symbol id="i-percent" viewBox="0 0 24 24"><path d="M19 5 5 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="7.5" cy="7.5" r="2.6" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16.5" cy="16.5" r="2.6" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
            <symbol id="i-doc" viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7V3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M13 3v5h5M10.5 13l2 2 3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
            <symbol id="i-radar" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-dasharray="3 3"/><path d="M12 12l6-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/></symbol>
            <symbol id="i-building" viewBox="0 0 24 24"><path d="M4 21V5l7-2v18M11 21h9V10l-6-2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 13h2m-2 4h2M7 9h1m-1 4h1m-1 4h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
            <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 20V4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 20h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m7 14 4-5 3 3 5-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="19" cy="5" r="1.6" fill="currentColor"/></symbol>
            <symbol id="i-phone" viewBox="0 0 24 24"><path d="M6 3h4l1.5 5-2.5 1.5a12 12 0 0 0 5.5 5.5L16 12.5l5 1.5v4a2 2 0 0 1-2 2A16.5 16.5 0 0 1 4 5a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
            <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3.5" y="5.5" width="17" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m5 8 7 6 7-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
            <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
            <symbol id="i-wa" viewBox="0 0 24 24"><path d="M12 3.5A8.5 8.5 0 0 0 4.9 16.3L3.5 21l4.8-1.3A8.5 8.5 0 1 0 12 3.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 8.6c.8 2.8 3.6 5.4 6.4 6l.8-1.8-2.2-1-1 .9a7.4 7.4 0 0 1-1.8-1.7l1-1-.8-2.2L9 8.6z" fill="currentColor"/></symbol>
        </defs>
    </svg>
</body>
</html>
