# SARAKI — Project Documentation

> Bilingual (French-first / English) fashion e-commerce and made-to-measure platform.
> Business: **SARAKI atelier, Niamey, Niger** — contemporary African fashion (boubous, caftans, wax ensembles), ready-to-wear catalogue + sur-mesure tailoring requests.
>
> This project serves **two purposes at once**:
> 1. **Client project** — the real SARAKI business platform, intended to go live on the client's own domain with their own payment provider.
> 2. **Group e-commerce course project** — hosted on the school server as the academic deliverable.

---

## Table of contents

1. [Stack & architecture](#1-stack--architecture)
2. [Feature implementation](#2-feature-implementation)
3. [Challenges encountered & how we solved them](#3-challenges-encountered--how-we-solved-them)
4. [Current deployment (school server)](#4-current-deployment-school-server)
5. [User documentation (admin guide summary)](#5-user-documentation-admin-guide-summary)
6. [Migration plan: school server → client domain](#6-migration-plan-school-server--client-domain)
7. [SMTP configuration](#7-smtp-configuration)
8. [Environment variable reference](#8-environment-variable-reference)

---

## 1. Stack & architecture

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3), Blade templates, Alpine.js |
| Frontend | Tailwind CSS 3.4, Vite build, custom design system (charcoal/gold/beige, Manrope + Playfair Display) |
| Database | MySQL 8 (production) / MariaDB (local dev) — **shared schema, `saraki_` table prefix** |
| Auth | Laravel Breeze (roles: `admin`, `customer`) |
| Payments | Paystack (card + mobile money), Cash on Delivery, bank transfer — toggled in admin settings |
| Email | Laravel Mail (SMTP in production, `log` driver until configured) |
| Queue/Cache/Session | `sync` / `file` / `file` — no Redis needed |
| Tests | Pest — **314 tests, ~1,190 assertions, all green** |

**Key architectural decisions**

- **Two product types**: `ready_to_wear` (variants, stock, cart → checkout → Paystack) and `made_to_measure` (no cart — customer sends a tailoring request that lands in admin → Messages, linked to the product with its photo).
- **Made-to-measure pricing**: separate `fabric_price` + `fabric_unit` (meter/pagne) + `confection_price` fields; the page shows the breakdown and notes that bringing your own fabric means only the confection price applies.
- **Bilingual**: French is the default locale, English supported. All storefront routes are `/{locale}/...` (`fr`|`en`); a locale switcher persists the choice in session + user preference and redirects to the same page in the other language.
- **Content is CMS-driven**: pages, journal posts, gallery, products, settings are all managed from `/admin` — no code changes needed for day-to-day content.

---

## 2. Feature implementation

### Storefront

| Feature | How it was implemented |
|---|---|
| Catalogue | `CatalogueController` with filters (category, search, sort, product-type tab). Ready-to-wear and sur-mesure never mix — separate tabs. |
| Product page | Gallery with thumbnails, variant picker (size/colour), stock-aware add-to-cart for ready-to-wear; request form (fabric choice SARAKI/own + note) for sur-mesure. |
| Hover carousel | Alpine.js component on product cards: auto-advances through all product images every 1.4 s with fade, resets on mouse-leave. Only activates with 2+ images. |
| Cart & checkout | Session-based cart, delivery zones from settings, order expiry (30 min default) released by cron. |
| Payments | `PaymentService` → Paystack initialize/verify/webhook; COD and transfer recorded manually by admin. |
| Appointments | Service + slot booking with lead time and horizon limits, admin calendar view, blocked dates, working hours. |
| Accounts | Breeze auth + profile, measurement sheets (`customer_measurements`), order history, wishlist. |
| Content | CMS pages (about/shipping/returns/privacy/terms), journal/blog, gallery — all bilingual (`_fr`/`_en` columns). |
| FAQ | Dedicated `/fr/faq` + `/en/faq` accordion with grouped Q&A, FAQPage JSON-LD, sitemap entry. Content in `lang/{fr,en}/faq.php`. |
| Contact & messages | Contact form + sur-mesure requests → `contact_messages` table → admin inbox with read/reply/status workflow. Sur-mesure messages link `product_id` and render a **photo card** of the chosen model instead of raw URLs. |
| WhatsApp | `whatsapp_number` setting (admin → Settings) powers floating button, footer link, contact CTA. Empty = hidden. |
| SEO | Canonical URLs, hreflang alternates, JSON-LD (Product, FAQPage, Organization), auto-generated `sitemap.xml`, Open Graph tags. |
| i18n | `SetLocale` middleware + `PathLocale`; translations in `lang/fr` + `lang/en`. |

### Admin (`/admin`)

Dashboard with stat cards, orders management (status workflow, payment reconciliation), catalogue (products, variants, images with drag-order + primary flag, categories), appointments calendar, customers + measurement sheets, content (pages/posts/gallery), messages inbox, settings (general, shipping zones, payment methods, social links).

### Platform hardening

Security headers middleware, rate limiting on forms/payments, honeypot spam protection, custom error pages (403/404/419/429/500/503) in both languages, image optimizer (WebP thumbnails via Intervention Image), automated backups (`backup:run` → gzipped mysqldump + uploads zip, 14-day retention), scheduled order expiry.

---

## 3. Challenges encountered & how we solved them

### 3.1 Language switcher didn't work

**Challenge:** clicking EN/FR in the header only called `back()` — the locale was never persisted and the URL prefix kept forcing the old language.

**Fix:** the locale route now saves `locale` to the session **and** syncs the signed-in user's preference, then redirects to the same page in the selected locale (`/fr/catalogue?sort=name` → `/en/catalogue?sort=name`). Regression tests added.

### 3.2 Made-to-measure requests showed raw URLs

**Challenge:** admin messages contained `Design photo: http://...` text instead of an image.

**Fix:** added `product_id` to `contact_messages` (FK, `nullOnDelete`), the request controller links the chosen product, and the admin message view renders a photo card (thumbnail, name, category, edit link). The message body was cleaned to remove URL lines; the existing production message was patched manually.

### 3.3 Sur-mesure needed two prices

**Challenge:** client requirement — one price for fabric (per meter **or** per pagne), one for making the garment.

**Fix:** three product columns (`fabric_price`, `fabric_unit`, `confection_price`), admin form fields, storefront price-breakdown display with an own-fabric note.

### 3.4 Business location was wrong (Accra/Ghana → Niamey/Niger)

**Challenge:** all copy assumed Ghana — hero text, FAQ delivery zones, CMS pages, timezone, shipping zones.

**Fix:** updated all FR/EN lang files, page seeders, settings seeders, `APP_TIMEZONE` default → `Africa/Niamey`, delivery zones → Niamey/regional capitals/other cities. **Currency kept as GHS and Paystack kept as-is per client decision** (Paystack doesn't support Niger/XOF — flagged for the migration phase).

### 3.5 Deployment: shared database collisions

**Challenge:** the school database `ecommerce_2026A_rafiatou_ali` already contains a **WordPress/WooCommerce site** (~100 `wp_*` tables) and an **older custom shop** (`brands`, `cart`, `orderdetails`, `tasks`…). Our migrations failed: `Table 'categories' already exists`.

**Fix:** Laravel `DB_PREFIX` support — set `DB_PREFIX=saraki_` so all our tables are `saraki_*`, completely isolated. This required patching **raw SQL** in 4 migrations (`ALTER TABLE …` statements bypass the prefix) and naming the fulltext index explicitly (auto-generated name exceeded MySQL's 64-char limit with the prefix). `backup:run` was also fixed to only dump `saraki_*` tables so backups never include WordPress data.

### 3.6 Deployment: Apache 500 before Laravel booted

**Challenge:** site returned 500 with an empty response and no Laravel log entries.

**Fix:** a debug `info.php` wrapper revealed `Permission denied` on `storage/logs` and `storage/framework/sessions` — Apache runs as `www-data` but uploaded files were owned by `rafiatou.ali`. Fixed with `chmod -R a+rwX storage bootstrap/cache`. Files created later by `www-data` showed "Operation not permitted" on chmod — that's expected and harmless.

### 3.7 Deployment: subdirectory install (`/~rafiatou.ali/`)

**Challenge:** no public domain — the site lives at `http://IP:442/~rafiatou.ali/` via the school's proxy (port 442 → Apache :80 → `mod_userdir` → `~/public_html` symlink → `~/saraki/public`).

**Fixes:**
- `public_html` moved aside and symlinked to `~/saraki/public` (old site kept as `public_html_old`)
- `APP_URL=http://169.239.251.102:442/~rafiatou.ali` — makes `asset()`, `Storage::url()`, `route()` generate correct URLs
- **Logo bug**: the logo component returned a root-relative `/brand/logo.png` which missed the `~user` prefix → changed to `asset()`. Audited all views — no other hardcoded absolute paths.

### 3.8 Gallery 500 — `only_full_group_by`

**Challenge:** MySQL 8's strict SQL mode rejected `ORDER BY sort_order` inside a `GROUP BY category` count query (local MariaDB was lenient, production MySQL 8 was not).

**Fix:** removed `orderBy` from `GalleryItem::scopePublished()` — the controller already applies explicit ordering for lists; the count query no longer carries it.

### 3.9 Composer/Node blocked on server

**Challenge:** `composer` and `npm` return "Please ask your administrator" — can't build on the server.

**Fix:** uploaded `vendor/` (51 MB) and `public/build/` pre-compiled from the dev machine via FileZilla/SFTP (port 422). `.env` created directly on the server — never uploaded.

---

## 4. Current deployment (school server)

| Item | Value |
|---|---|
| URL | `http://169.239.251.102:442/~rafiatou.ali/` |
| SSH | `ssh -p 422 rafiatou.ali@169.239.251.102` |
| App path | `~/saraki` (Laravel root); web root = `~/public_html` → symlink to `~/saraki/public` |
| Database | `ecommerce_2026A_rafiatou_ali` on localhost MySQL 8, tables prefixed `saraki_` |
| phpMyAdmin | `http://169.239.251.102:442/phpmyadmin` |
| Cron | `* * * * * cd /home/rafiatou.ali/saraki && php artisan schedule:run` (order expiry every 5 min, backups daily) |
| Payments | **Paystack TEST mode** (`pk_test_`/`sk_test_`) — card + mobile money |
| Email | `MAIL_MAILER=log` — emails written to `storage/logs`, **not sent** until SMTP is configured |
| Backups | `storage/app/backups/` — download periodically via FileZilla (same-server copies aren't a real backup) |

**Admin access:** `/admin` — email = `STORE_ADMIN_EMAIL`, password = `SEED_ADMIN_PASSWORD` set in `.env`.

---

## 5. User documentation (admin guide summary)

Full guide: `docs/admin-guide.md`. Daily routine:

- **Dashboard**: orders needing attention, today's appointments, unread messages.
- **Orders**: update status (confirmed → ready → delivered), confirm COD/transfer payments received, process refunds.
- **Catalogue**: add/edit products — set type (prêt-à-porter / sur mesure), upload multiple photos (first = primary, drag to reorder), manage variants (size/colour/stock/price).
- **Sur-mesure**: watch Messages for tailoring requests — each shows the chosen model's photo, fabric choice (SARAKI or customer's own), and their note. Reply by email or update status.
- **Appointments**: calendar view, confirm/cancel bookings, manage services and working hours.
- **Content**: edit CMS pages, publish journal posts, manage gallery.
- **Settings**: store name/address, WhatsApp number, Google Maps embed, opening hours, shipping zones + fees, payment method toggles, social links.

---

## 6. Migration plan: school server → client domain

When SARAKI is ready to go live on the client's own infrastructure, here's the full task list.

### Phase A — Domain & hosting

| # | Task | Details |
|---|---|---|
| A1 | **Choose hosting** | VPS (recommended: Laravel-friendly, SSH access, PHP 8.3+, MySQL 8) or managed Laravel hosting. Avoid shared hosting without SSH — composer/artisan are required. |
| A2 | **Register domain** | e.g. `saraki.bj` / `.com` — client's choice. Point DNS A record to server IP. |
| A3 | **Provision server** | Ubuntu 24.04, Apache/Nginx + PHP 8.3-FPM, MySQL 8, Composer, Node (for builds) or upload pre-built assets. |
| A4 | **SSL certificate** | Let's Encrypt (Certbot) once DNS resolves — **required for live payments**. |
| A5 | **Deploy code** | Git clone or FileZilla upload (same process as school server); `composer install --no-dev`, `npm ci && npm run build` on server or upload `public/build` + `vendor`. |
| A6 | **Database** | Fresh database (no need for prefix on a dedicated DB, but keeping `saraki_` is harmless — just keep `DB_PREFIX` consistent or drop it and re-migrate). Import via SQL dump or fresh `migrate --seed`. |
| A7 | **Web server config** | Document root → `src/public` (proper vhost, not userdir). Enable `mod_rewrite`. |
| A8 | **Cron** | Same `schedule:run` entry. |
| A9 | **Backups off-server** | Configure `BACKUP_PATH` + copy dumps to cloud storage (S3/B2) or the client's machine — local-only backups don't survive server loss. |

### Phase B — Payments (the big change)

**Current state:** Paystack test mode, GHS currency, Ghana-oriented. **Paystack does not support Niger** — no XOF, no Niger mobile money (Airtel Money, Moov).

| # | Task | Details |
|---|---|---|
| B1 | **Choose payment provider** | Options: (a) a Niger/Africa aggregator supporting XOF mobile money — research current options (e.g., regional fintechs, Flutterwave coverage for Niger, or local aggregators); (b) keep card-only via a provider that accepts Niger-issued cards; (c) COD + bank transfer only initially. **Needs client decision.** |
| B2 | **Currency decision** | Switch `STORE_CURRENCY` from `GHS` to `XOF` (CFA franc) — update `.env`, confirm `money()` formatting renders correctly (`fr` locale may need `fr_SN`/`fr_NE` instead of `fr_GH` for correct XOF symbol — test and adjust `STORE_CURRENCY_LOCALE_*`). Re-price all products in XOF. |
| B3 | **Implement/replace gateway** | If not Paystack: write a new `PaymentGateway` implementation (the codebase isolates payments behind a service — check `app/Services/`), webhook handler, and checkout channel config. |
| B4 | **Live keys + webhook** | Production keys in `.env`, webhook URL registered with the provider (`/webhooks/paystack` or new endpoint), HTTPS verified. |
| B5 | **Test end-to-end** | Real small transaction, refund flow, webhook delivery, order status reconciliation. |

### Phase C — Content & configuration

| # | Task | Details |
|---|---|---|
| C1 | Real product catalogue — photos, FR+EN descriptions, XOF prices (admin uploads) |
| C2 | Store address, Google Maps embed, opening hours, WhatsApp number (Niamey number!) |
| C3 | Shipping zones re-check — fees in XOF, delivery partners in Niamey |
| C4 | CMS pages review — currency mentions (GH₵→FCFA), payment methods text, legal entity name |
| C5 | FAQ payment/delivery answers updated for the real provider |
| C6 | Admin accounts — create client admin user, hand over credentials |
| C7 | Email templates — verify sender name/address, test all notification flows |

### Phase D — Go-live checklist

- [ ] DNS + SSL live, `APP_URL` updated to `https://domain`
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Live payment keys + webhook verified with a real transaction
- [ ] SMTP sending real mail (test order confirmation, password reset)
- [ ] Cron running, first backup verified off-server
- [ ] UAT script (`docs/uat/uat-script.md`) re-run on production
- [ ] Client trained on admin panel (`docs/admin-guide.md`)

---

## 7. SMTP configuration

Transactional email (order confirmations, appointment reminders, contact/tailoring notifications, password resets) requires SMTP. **It is configured in `.env` on the server — there is no admin UI for it.**

### Option A — Gmail (quickest for testing)

1. Enable 2-Step Verification on the Google account
2. Create an **App Password**: Google Account → Security → App passwords → generate one (16 chars)
3. In `~/saraki/.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=youremail@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx   # the app password, spaces ok
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"   # must be a real address or Gmail rewrites it
MAIL_FROM_NAME="${APP_NAME}"
```

Then `php artisan config:cache` and test:

```bash
php artisan tinker --execute="Mail::raw('Test', fn(\$m) => \$m->to('you@example.com')->subject('SMTP test'));"
```

### Option B — School/provider SMTP relay

Use whatever host/port/credentials your admin or email provider gives (common: `smtp.school.edu`, port 587/465, TLS/SSL). Same `.env` keys — just change host/port/scheme (`MAIL_SCHEME=ssl` + `MAIL_PORT=465` for implicit SSL).

### Option C — Transactional service (recommended for production)

Brevo, Mailgun, Amazon SES, or similar — free tiers cover a small shop. Gives better deliverability than Gmail and a proper `MAIL_FROM_ADDRESS` on your domain.

**Verify after configuring:** place a test order → confirmation email arrives; register a new account → verification/reset email arrives; send a contact message → both customer and admin notifications arrive.

---

## 8. Environment variable reference

Key `.env` values (server copy lives at `~/saraki/.env`, never committed):

```env
APP_NAME="SARAKI"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://169.239.251.102:442/~rafiatou.ali   # → https://domain on migration
APP_TIMEZONE=Africa/Niamey
APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ecommerce_2026A_rafiatou_ali
DB_USERNAME=...
DB_PASSWORD=...
DB_PREFIX=saraki_            # REQUIRED on the shared school DB; optional on dedicated DB

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

STORE_NAME=SARAKI
STORE_CURRENCY=GHS          # → XOF on migration
STORE_CURRENCY_LOCALE_FR=fr_GH   # → review for XOF (fr_NE/fr_SN)
STORE_CURRENCY_LOCALE_EN=en_GH
STORE_ADMIN_EMAIL=admin@saraki.test
SEED_ADMIN_PASSWORD=...     # only used by db:seed
ORDER_EXPIRY_MINUTES=30

PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_CHANNELS=card,mobile_money
PAYSTACK_BASE_URL=https://api.paystack.co

MAIL_MAILER=smtp             # once configured — see §7
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="${APP_NAME}"
```

---

*Last updated: 2026-11-15 — deployed to school server, Paystack test mode, SMTP pending.*
