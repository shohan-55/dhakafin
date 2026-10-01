# DhakaFin

DhakaFin is a Bangladesh-focused **Professional Services + SaaS Financial & Compliance Platform**.

## Product pillars

- Accounting & bookkeeping
- Statutory audit support
- Corporate & personal tax
- VAT / VDS compliance
- Virtual CFO and management reporting
- TDS / VDS calculators and workflow
- Tax & VAT deadline calendar
- Mushak generators and records
- Client / staff workspaces
- Documents, engagements, reminders and evidence
- Billing and subscription SaaS
- Payroll, inventory and purchasing
- APIs, webhooks and AI/OCR-assisted workflows

## Development

Active branch: `build/phase-01-foundation`

Current foundation includes Laravel 13, PHP 8.3+, Tailwind 4, tenant-aware organizations, organization memberships, compliance obligations, a deterministic withholding calculator, public homepage, architecture guardrails and baseline tests.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate
npm run build
php artisan test
php artisan serve
```

On Windows PowerShell, use `Copy-Item .env.example .env`.

## Completion standard

DhakaFin is only "100% complete" after all agreed SaaS and professional-service modules are implemented, authorization and tenancy are verified, clean migrations pass, critical workflows have automated tests, production deployment is documented, and a final regression/security audit passes.
