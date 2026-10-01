# DhakaFin Architecture

DhakaFin is a Bangladesh-focused professional-services and finance-operations platform.

## Core modules
- Identity, MFA, tenancy and RBAC
- Public website, CMS, SEO and media
- Client/staff workspaces
- Engagements and protected documents
- Billing, invoices, payments, credits and refunds
- Accounting / double-entry subledger
- Tax and VAT workspaces
- Compliance obligations, reminders and notifications
- Reporting and management information
- Payroll
- Inventory and purchases
- API, webhooks and external sharing
- OCR / AI-assisted drafting with mandatory human verification

## Technical baseline
Laravel 13, PHP 8.3+, Livewire 4 for application workspaces, Tailwind CSS 4, MySQL 8, queues for long-running jobs, private document storage, tenant-scoped authorization, immutable audit events, integer minor units for money and idempotency for externally-impacting operations.

## Regulatory wording
DhakaFin may provide statutory-audit preparation and support. Public and in-app copy must not imply that DhakaFin signs a statutory audit opinion unless that work is performed by an appropriately authorized audit firm/professional.
