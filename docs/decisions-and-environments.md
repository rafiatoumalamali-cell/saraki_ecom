# SARAKI — Decisions and Environment Register

| Field | Value |
|---|---|
| Project | SARAKI Digital Platform |
| Document | Confirmed decisions, environment status, client-supplied inputs |
| Version | 0.1 |
| Date | 2026-11-05 |
| Status | In review (Task 0.7) |
| Gate | Feeds **G0-final** |

This is the record the build is measured against. Anything not confirmed here is either a default from the SRS or explicitly pending. A pending item never blocks code that does not depend on it; it blocks the related phase gate.

---

## 1. Verified local environment

Checked on this workstation on 2026-11-05.

| Component | Required | Found | Status |
|---|---|---|---|
| PHP | 8.2+ | **8.3.28** (CLI, ZTS, VC++ 2019 x64) | OK |
| Composer | 2.x | **2.10.2** | OK |
| MySQL | 8.0+ | **8.4.3** (Laragon) | OK |
| Node.js | 18+ (Vite) | **26.2.0** | OK |
| PHP extensions | pdo_mysql, mbstring, openssl, curl, fileinfo, gd, zip, intl, bcmath | all present | OK |
| Upload limits | ≥ 20 MB for product images | `upload_max_filesize` 2G, `post_max_size` 2G | OK |
| Tooling | Laragon on Windows 11 | present | OK |

Notes:

- The local PHP build is **ZTS** (thread-safe). That is fine for Laragon development; production should use a standard **NTS** build.
- `max_execution_time` is `0` in CLI, which is expected. Web requests will use the Laragon value; image processing will keep uploads modest (≤ 8 MB, resized server-side).
- Vite is used for assets in development; production build is `npm run build`.

**Local services still to create in Phase 1**

| Item | Plan |
|---|---|
| Database | `saraki_local`, `utf8mb4_unicode_ci`, user `saraki` with local-only password |
| Mail | Laravel log driver in local (`.env` `MAIL_MAILER=log`) until SMTP credentials arrive |
| Storage | `public` disk symlink for product images and CMS media |
| Queue | **Not used** — synchronous mail, file cache, no worker required |
| Scheduler | Laravel scheduler via `php artisan schedule:work` locally; cron in production |

---

## 2. Decision register

Status values: **Confirmed** (approved by client), **Default** (SRS/plan default, no objection raised), **Open** (client input required).

| ID | Decision | Status | Owner | Needed by |
|---|---|---|---|---|
| D-01 | Stack: Laravel 11, PHP 8.2+, MySQL 8, Blade + Tailwind + Alpine.js; no SPA, queues, Redis, Docker, or admin packages | **Confirmed** 2026-11-05 | Client | Done |
| D-02 | Product variants in v1 (size/colour, per-variant stock, SKU, optional price delta) | **Confirmed** 2026-11-05 | Client | Done |
| D-03 | Paystack payments in v1, card + mobile money, hosted checkout, server-side verification, signed webhook | **Confirmed** 2026-11-05 | Client | Done |
| D-04 | Customer reviews deferred to v1.1 | **Default** (SRS §9) | Client | G0-final |
| D-05 | Roles: `admin`, `customer` only | **Default** | Client | G0-final |
| D-06 | French default, English toggle; content entered in both languages | **Default** | Client | G0-final |
| D-07 | WhatsApp click-to-chat (`wa.me`) + Google Maps embed | **Default** | Client | G0-final |
| D-08 | Single studio calendar, admin approval; consultation 30m, measurement 45m, fitting 45m; Mon–Sat 09:00–18:00 | **Default** | Client | G4 |
| D-09 | Market v1: Ghana, GHS, tax-inclusive pricing; currency/locale are configuration values for a later XOF switch | **Confirmed** 2026-11-05 | Client | Done |
| D-10 | Order statuses: pending → confirmed → in production → ready → delivered; cancelled. Payment status separate | **Default** | Client | G3 |
| D-11 | Hosting: TBD; PHP 8.2+ NTS, MySQL 8, SSL, cron, document root `public/` | **Open** | Client | Day 3 |
| D-12 | Brand assets: TBD; placeholders until logo, photography, and copy are supplied | **Open** | Client | Day 2 |
| D-13 | Timeline: 19 working days (~4 weeks) | **Confirmed** 2026-11-05 | Client | Done |
| D-14 | Paystack Ghana merchant account, GHS, card + mobile money; test keys and webhook secret by Day 4 | **Confirmed** 2026-11-05 (account details pending) | Client | Day 4 |

### 2.1 Settings driven by these decisions

| Setting key | Initial value | Changeable in admin |
|---|---|---|
| `currency` | `GHS` | Yes (no code change) |
| `default_locale` | `fr` | Yes |
| `supported_locales` | `fr,en` | No (code-level) |
| `tax_display` | `inclusive` | Yes |
| `shipping_zones` | Accra 35 GHS; other regions per table | Yes |
| `payment_methods` | `paystack`, `cod` (off by default) | Yes |
| `order_expiry_minutes` | `30` | Yes |
| `appointment_hours` | Mon–Sat 09:00–18:00 | Yes |
| `whatsapp_number` | pending client | Yes |
| `map_embed` | pending client | Yes |

---

## 3. Client-supplied inputs

| # | Input | Purpose | Needed by | Status |
|---|---|---|---|---|
| 1 | Logo (SVG or high-res PNG) + monogram | Header, footer, invoices, favicon | Day 2 | Pending |
| 2 | Product photography and descriptions (FR + EN) | Catalogue seed and UAT | Day 5 | Pending |
| 3 | Product sizes, colours, prices (GHS), stock counts | Catalogue and variants | Day 5 | Pending |
| 4 | Paystack **test** public/secret keys + webhook secret | Payment integration | Day 4 | Pending |
| 5 | SMTP credentials (host, port, user, password, from-address) | Transactional email | Day 4 | Pending |
| 6 | Domain name and DNS access | Staging and production URLs, SSL | Day 3 | Pending |
| 7 | Hosting account (or approval to use a recommended provider) | Deployment | Day 3 | Pending |
| 8 | Shipping zones and fees (Ghana) | Checkout totals | Day 6 | Pending |
| 9 | WhatsApp number, showroom address, Google Maps place/link | Contact and footer | Day 6 | Pending |
| 10 | Legal copy: terms, privacy, returns | Legal pages, checkout consent | Day 12 | Pending |
| 11 | Studio working hours and blocked dates | Appointment calendar seed | Day 11 | Pending |
| 12 | Appointment service list, prices, durations | Booking page | Day 11 | Pending |

Rules:

- Only **test** Paystack keys until go-live. Live keys are entered by the client in the production environment, never shared in chat or committed.
- `.env` is never committed. `.env.example` carries empty placeholders only.
- Until assets arrive, the build uses placeholders that match the design system; swapping them must not require layout changes.

---

## 4. External service status

| Service | Purpose | Status | Notes |
|---|---|---|---|
| Paystack | Card and mobile money | Account confirmed in Ghana; keys pending | Test mode until launch; webhook endpoint `/webhooks/paystack`, HMAC SHA512, idempotent by event ID |
| SMTP provider | Order, payment, appointment, contact email | Pending | Local uses `log` driver; production requires SPF/DKIM |
| Domain + SSL | Public site | Pending | Force HTTPS; HSTS after launch |
| Hosting | PHP 8.2+ NTS, MySQL 8 | Pending | Needs cron and writable `storage/` |
| Google Maps | Showroom embed | Pending | No API key required for basic embed |
| WhatsApp | Click-to-chat | Pending | `wa.me` link only; no Business API |
| Backups | Daily DB + uploads | Planned | Local `backups/`; production off-site copy recommended |

**Not used in v1:** Redis, queues/workers, Docker, CDN (optional later), SMS API, shipment tracking API, analytics vendor (optional privacy-friendly script).

---

## 5. Deployment targets

| Environment | URL | Purpose | Data |
|---|---|---|---|
| Local | `http://saraki.test` (Laragon) | Development | Seed/fake data |
| Staging | TBD | Client review and UAT | Copy of seed data, Paystack test mode |
| Production | TBD | Live store | Real data, Paystack live mode |

Promotion path: local → staging (UAT sign-off, G6) → production (G7). No direct local-to-production deploys.

---

## 6. Open questions that do not block Phase 1

These are recorded so they are not lost. Each is answered before the phase that needs it; none prevents repository setup, Laravel install, schema, models, or layouts.

1. **Hosting provider** — shared cPanel vs VPS. Affects only Task 7.x. Requirement: PHP 8.2+ NTS, MySQL 8, SSL, cron, `public/` document root.
2. **Brand assets** — placeholder logo/typography used until supplied.
3. **GHS price list and shipping fees** — seeded with representative values for UAT, replaced before launch.
4. **Tax display** — assumed **inclusive** (common in Ghana retail); configurable in settings.
5. **Refund policy** — assumed admin-initiated full/partial refunds through Paystack, no automatic restock.
6. **COD availability** — implemented but **off by default**; enabled per zone in settings.

---

## 7. G0-final sign-off

Gate G0-final closes Phase 0. It requires:

- [ ] Task 0.3 SRS archive approved
- [ ] Task 0.4 ERD approved
- [ ] Task 0.5 data dictionary approved
- [ ] Task 0.6 design tokens and UI kit approved
- [ ] Task 0.7 decisions and environments confirmed
- [ ] No open blocker for Phase 1 (D-11 and D-12 may remain open; they are needed by Day 2–3, not Day 1)

| Gate | Date | Approved by | Notes |
|---|---|---|---|
| G0-final | 2026-11-05 | Rafiatou Malam Ali (PM) | Approved; D-11/D-12 remain open (needed Day 2–3) |
