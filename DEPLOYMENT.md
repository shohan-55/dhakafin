# Deployment — cPanel (production plan)

Confirmed hosting environment (user-provided, 2026-09-19):

- **Hosting:** cPanel — 4 GB RAM, 50 GB SSD
- **PHP:** 8.3+ available (MultiPHP / Select PHP Version) — satisfies `^8.3` requirement
- **Database:** MySQL (created via cPanel → MySQL Database Wizard)
- **Deployment timing:** postponed by user — prepare package when asked

## Production footprint (approx.)

| Piece | Size |
|---|---|
| App source | ~3 MB |
| vendor/ (pre-built, upload as-is) | ~35 MB |
| public/assets-compiled/ | ~1 MB |
| **Total** | **~40 MB** |

No composer or node needed on the server — everything is built here in the sandbox.

## Deploy package recipe (when requested)

1. Build a ZIP containing: full repo **minus** `.git`, `node_modules`, `tools_runtime`, `storage/logs/*`, plus `vendor/` and `public/assets-compiled/`.
2. Create production `.env.example` variant with:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://dhakafin.com`
   - `DB_CONNECTION=mysql` + placeholder host/db/user/pass values from cPanel
3. Upload ZIP → cPanel File Manager → extract outside public_html (e.g. `/home/<cpuser>/dhakafin/`).
4. Point domain document root to `/home/<cpuser>/dhakafin/public` (cPanel → Domains → Document Root).
5. Copy `.env`, fill `APP_KEY` (generate locally: `php -r "echo 'base64:'.base64_encode(random_bytes(32));"`), DB credentials.
6. Run `php artisan migrate --force` (SSH Terminal in cPanel, or a one-time web route if no SSH).
7. Clear + cache config: `php artisan config:cache route:cache view:cache`.
8. SSL: cPanel → AutoSSL → run (free HTTPS on dhakafin.com).
9. Cron (optional, scheduler not currently used): skip.

Rollbacks: keep previous ZIP on hosting; swap document root back if needed.
