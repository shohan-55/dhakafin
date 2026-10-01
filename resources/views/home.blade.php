<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="DhakaFin — accounting, tax, VAT, audit-support and virtual CFO services for Bangladesh businesses.">
<title>DhakaFin — Finance, Tax & Compliance</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<nav class="nav"><div class="shell navin"><a class="brand" href="{{ route('home') }}"><span class="mark">DF</span><span>DhakaFin</span></a><div class="links"><a href="#services">Services</a><a href="#tools">Tools</a><a href="#contact">Contact</a></div><a class="btn primary" href="#contact">Talk to DhakaFin</a></div></nav>
<main>
<section class="hero"><div class="shell hero-grid">
<div><span class="kicker">Finance clarity for Bangladesh businesses</span><h1>Finance with <span class="grad">control, confidence & clarity.</span></h1><p class="lead">DhakaFin combines professional accounting, tax, VAT, statutory-audit support and virtual CFO services with practical digital tools and a secure client workspace.</p><div class="actions"><a class="btn primary" href="#services">Explore services</a><a class="btn ghost" href="#tools">See digital tools</a></div></div>
<aside class="panel"><small>DhakaFin command view</small><h3>One finance workspace.</h3><div class="mini-grid"><div class="mini"><strong>Accounting</strong><span>Books, reconciliations and management information.</span></div><div class="mini"><strong>Tax + VAT</strong><span>Obligations, filings and withholding workflows.</span></div><div class="mini"><strong>Audit Support</strong><span>Evidence, schedules and review status.</span></div><div class="mini"><strong>vCFO</strong><span>Budget, cash flow and decision support.</span></div></div></aside>
</div></section>
<section class="section" id="services"><div class="shell"><div class="eyebrow">Professional services</div><h2>Built around the finance work businesses actually need.</h2><div class="cards">@foreach($services as $index=>$service)<article class="card"><small>{{ str_pad((string)($index+1),2,'0',STR_PAD_LEFT) }}</small><h3>{{ $service }}</h3><p>Structured, evidence-led delivery designed for growing organizations in Bangladesh.</p></article>@endforeach</div></div></section>
<section class="section" id="tools"><div class="shell band"><div class="eyebrow">DhakaFin tools</div><h2>Practical compliance tools, not dashboard decoration.</h2><div class="tools"><div class="tool"><strong>TDS / VDS Calculator</strong><span class="pill">Planned</span></div><div class="tool"><strong>Mushak 6.3 Generator</strong><span class="pill">Planned</span></div><div class="tool"><strong>Tax & VAT Calendar</strong><span class="pill">Foundation</span></div><div class="tool"><strong>Deadline Reminders</strong><span class="pill">Planned</span></div><div class="tool"><strong>Client Document Workspace</strong><span class="pill">Planned</span></div><div class="tool"><strong>Management Reports</strong><span class="pill">Planned</span></div></div></div></section>
<section class="section" id="contact"><div class="shell"><div class="cta"><div><div class="eyebrow" style="color:#8ee0d0">DhakaFin</div><h2>Professional finance support without spreadsheet chaos.</h2><p>The secure client workspace and full application modules are being built on this foundation.</p></div><a class="btn primary" href="mailto:business@dhakafin.com">business@dhakafin.com</a></div></div></section>
</main>
<footer class="footer"><div class="shell foot"><span>© {{ date('Y') }} DhakaFin. All rights reserved.</span><span>Accounting · Tax · VAT · Audit Support · Virtual CFO</span></div></footer>
</body></html>
