# DhakaFin — dhakafin.com

**Compliant. Leak-free. In control.**

Professional accounting, audit, tax, VAT & financial advisory for individuals, startups and SMEs in Bangladesh — with the flagship **CostGuard** cost-leakage review.

## Stack

- **Laravel 13** (PHP 8.3+)
- **Blade** views + custom design system (handcrafted CSS — no framework)
- **Three.js** particle-globe hero (WebGL, graceful fallback)
- **GSAP + ScrollTrigger** scroll choreography
- **Lenis** smooth scrolling
- **Vite 8** asset pipeline
- **SQLite** (contact messages); swap to MySQL via `.env` for production

## Services (7 practice areas)

1. Accounting & Bookkeeping 🥇 2. Audit & Assurance 🥈 3. Tax Services 🥉
4. VAT Services 5. Cost Efficiency & Internal Control (CostGuard) 6. Corporate Compliance 7. Financial & Business Advisory

## Run locally

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build        # or: npm run dev
php artisan serve
```

## Structure

- `config/dhakafin.php` — all service/business data (single source of truth)
- `resources/views/` — layout, home, service, about, pricing, contact
- `resources/css/app.css` — full design system
- `resources/js/app.js` — 3D scene, animations, interactions
- `BUSINESS_PLAN.md` — the business plan this site is built from
