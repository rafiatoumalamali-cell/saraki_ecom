# SARAKI — Deployment Runbook

Production deployment for the Laravel app in `src/`. Local/XAMPP setup is in
`deploy/xampp-setup.md`; this runbook covers a real host (cPanel-style shared
hosting or a VPS — adjust paths accordingly).

**Promotion path:** local → staging (UAT sign-off, gate G6) → production (G7).
Never deploy straight from a developer machine to production.

---

## 1. Server requirements

| Requirement | Notes |
|---|---|
| PHP 8.3+ | extensions: `pdo_mysql`, `gd`, `intl`, `fileinfo`, `exif`, `mbstring`, `openssl`, `curl`, `zip`, `bcmath` |
| MySQL 8 (or MariaDB 10.4+) | one database + one user |
| Document root | must point at `src/public` |
| HTTPS | mandatory — set `SESSION_SECURE_COOKIE=true` |
| Cron | one entry hitting `php artisan schedule:run` every minute |
| Outbound HTTPS | required for Paystack API and SMTP |

## 2. Environment checklist (`src/.env` on the server)

Copy `.env.example` and set **every** value — nothing may stay `local`/empty
in production:

| Variable | Production value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` — never `true` in production |
| `APP_KEY` | `php artisan key:generate` output (keep secret, never commit) |
| `APP_URL` | `https://the-real-domain` — feeds canonical, sitemap, robots, emails |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `fr` / `en` |
| `DB_*` | production database credentials |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS) |
| `MAIL_*` | real SMTP (host/port/user/password); `MAIL_FROM_ADDRESS` a monitored mailbox; configure SPF/DKIM on the domain or mail lands in spam |
| `PAYSTACK_PUBLIC_KEY` / `PAYSTACK_SECRET_KEY` | **live** `pk_live_`/`sk_live_` keys at go-live; `sk_test_`/`pk_test_` only on staging |
| `PAYSTACK_CHANNELS` | `card,mobile_money` |
| `PAYSTACK_BASE_URL` | `https://api.paystack.co` |
| `STORE_*` | real store name, currency locales, admin email |
| `ORDER_NUMBER_PREFIX`, `ORDER_EXPIRY_MINUTES`, `APPOINTMENT_*` | confirm business values |
| `LOG_LEVEL` / `LOG_DAILY_DAYS` | `error` / `14` reasonable defaults |
| `BACKUP_*` | `BACKUP_MYSQLDUMP`/`BACKUP_MYSQL` full paths if binaries aren't on `PATH`; `BACKUP_KEEP_DAYS=14` |

## 3. Deploy steps

```bash
cd src
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # builds into public/build
php artisan migrate --force
php artisan db:seed --force      # first deploy only — seeds settings/pages; skip on updates
php artisan storage:link         # public disk → storage/app/public
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan event:cache          # optional
```

Permissions: `storage/` and `bootstrap/cache/` writable by the PHP user;
`storage/app/public` holds all uploads (product photos, covers, avatars).

## 4. Cron (required)

Without the scheduler, unpaid orders never release stock and **daily backups
never run**. Add to the server crontab (cPanel → Cron Jobs):

```cron
* * * * * cd /home/USER/saraki/src && php artisan schedule:run >> /dev/null 2>&1
```

Verify: `php artisan schedule:list` should show `orders:release-expired`
(every 5 min) and `backup:run` (daily). Backups land in
`storage/app/backups/` — **copy them off-server** (download, cloud sync, or
host backup) since local retention is only 14 days and the directory is not
web-accessible.

## 5. Paystack go-live

1. Swap test keys for **live** keys in `.env`, `php artisan config:cache`.
2. In the Paystack dashboard set the **webhook URL**:
   `https://DOMAIN/webhooks/paystack` — the endpoint verifies the
   `x-paystack-signature` HMAC and re-verifies server-side; without it orders
   still confirm via the customer callback, but async events (refunds,
   disputes) need the webhook.
3. Smoke-test one real small payment, then refund it from `/admin/orders`.

## 6. Go-live checklist

- [ ] `APP_URL` is the HTTPS domain; `APP_DEBUG=false`
- [ ] `php artisan about` shows production env
- [ ] Homepage, catalogue, product page, gallery, journal, contact → 200
- [ ] Register → login → cart → checkout with each enabled method
- [ ] One live Paystack card **and** one mobile-money payment succeed
- [ ] Webhook registered in Paystack dashboard; test event returns 200
- [ ] `/admin` login works; dashboard shows no payment warning banner
- [ ] `/sitemap.xml` and `/robots.txt` serve correctly
- [ ] Cron entry present; `schedule:list` correct; a manual
      `php artisan backup:run` writes both archives
- [ ] SMTP test: place a test order → emails arrive (order confirmation,
      payment receipt) and contact form reaches the admin inbox
- [ ] `storage:link` done; an uploaded admin image displays on the storefront

## 7. Rollback

- Code: redeploy the previous release/tag; keep `composer.lock` + `package-lock.json` committed.
- Database: take a `backup:run` snapshot **before** every `migrate --force`;
  restore with `php artisan backup:restore db --force` if a migration must be reverted
  (restore uploads too if files were deleted).
- DNS/SSL: keep the previous host reachable until smoke tests pass.

## 8. Handover package

Delivered with the site: this runbook, `docs/admin-guide.md`,
`docs/uat/uat-script.md`, `docs/uat/bug-log.md`, and the credential transfer
(listed in the project handover note — passwords stored in the client's
password manager, never in the repo or chat).
