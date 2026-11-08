# SARAKI Digital Platform — Master Build Plan

| Field | Value |
|---|---|
| Project | SARAKI Digital Platform |
| Client | SARAKI |
| Prepared by | Qoder for SahelSoft |
| Source requirements | SRS v1.0 (July 2026, SahelSoft) |
| Plan version | 0.7 — Laravel 13; Phases 1–5 done, gates G1–G5 awaiting sign-off |
| Date | 2026-11-05 |
| Target | Production-ready v1 in 19 working days (~4 weeks), variants + Paystack (Ghana/GHS) included |
| Status | G0 and G0-final approved; Phases 1–5 done, gates G1–G5 awaiting sign-off |

---

## 1. How this plan works

1. Every task has an **ID**, a **deliverable**, **acceptance criteria**, and an **indicative effort**.
2. Tasks move through these statuses:

   `Proposed → Approved → In progress → In review → Done` (or `Blocked`)

3. **No task starts until you approve it.** You can approve one task, a group, or a whole phase.
4. Every phase ends at a **review gate**. The next phase does not start until you sign off the gate.
5. If a task exceeds its estimate by more than ~30%, work stops and we decide together: extend, reduce scope, or defer to v1.1.
6. New requirements discovered during the build go to the **Change Log** and the v1.1 backlog unless you explicitly approve them for v1.

**Approval reply formats:**

- `Approve 0.4` — approves one task.
- `Approve Phase 1 (1.1–1.12)` — approves the whole phase.
- `Approve G0-final` — signs off the Phase 0 deliverables gate.
- `Change 3.5: ...` — requests a change before approval.

---

## 2. Decisions and assumptions

| ID | Decision | Status | Impact if changed |
|---|---|---|---|
| D-01 | **Stack:** Laravel 13 (PHP 8.3+), MySQL 8, Blade + Tailwind + Alpine.js. No SPA, queues, Redis, Docker, or admin packages. | **Approved 2026-11-05; changed 11 → 13 on 2026-11-05** | Changing stack reworks Task 1.2 and folder layout; later tasks remain valid. |
| D-02 | **Product variants:** included in v1 — size/colour options with per-variant stock, SKU, and optional price difference. | **Approved 2026-11-05** | Removing variants saves ~1.5–2 days but weakens the fashion catalogue. |
| D-03 | **Payments:** included in v1 via **Paystack** — card and mobile money required. Hosted checkout, server-side verification, signed webhooks. COD/transfer remain optional offline methods. | **Approved 2026-11-05** | Removing Paystack saves ~4 days but contradicts the client requirement. |
| D-04 | **Reviews:** deferred to v1.1 (SRS §6 lists the entity, §9 defers the feature). | Approved by default; remains per SRS §9 | Adds ~1–2 days including moderation. |
| D-05 | **Roles:** `admin` and `customer` only in v1. | Approved by default | Staff/fulfilment roles require extra authorization work. |
| D-06 | **Languages:** French default, English toggle; product/blog/page content entered in both languages. | Approved by default | English-only or UI-only translation reduces content-entry work. |
| D-07 | **WhatsApp & Maps:** click-to-chat (`wa.me`) link and embedded showroom map. | Approved by default | WhatsApp Business API adds cost, approval, and 1–2 days. |
| D-08 | **Appointments:** single studio calendar; admin approval required; consultation 30m, measurement 45m, fitting 45m; Mon–Sat 09:00–18:00. | Approved by default | Multiple staff calendars require a resource model. |
| D-09 | **Market (v1):** Ghana, GHS, tax-inclusive prices; Ghana delivery zones configurable in settings. Currency and locale are configuration values so the store can switch to Niger/XOF later. | **Updated 2026-11-05** | Switching to XOF later requires price re-entry, shipping-zone updates, and a Paystack account/entity enabled for XOF. |
| D-10 | **Order statuses:** pending → confirmed → in production → ready → delivered; cancelled. Payment status tracked separately. | Approved by default | Status changes affect tracking, emails, and stock rules. |
| D-11 | **Hosting:** TBD; requires PHP 8.3+, MySQL 8, SSL, cron, document root `public/`. | Open — needed by Day 3 | Shared hosting may block scheduler/backups or performance targets. |
| D-12 | **Brand assets:** TBD; placeholders until logo, photos, and copy are supplied. | Open — needed by Day 2 | Late assets delay launch content, not code. |
| D-13 | **Timeline:** **19 working days (~4 weeks)**. Includes variants, Paystack, and deeper polish. Reviews remain deferred per SRS §9. | **Approved 2026-11-05** | This is the selected schedule; scope changes trigger change control. |
| D-14 | **Paystack account (v1):** Ghana merchant account, GHS, card + mobile money channels. Test keys and webhook access required by Day 4. The integration reads currency from config so a later XOF switch does not require code changes. | **Updated 2026-11-05** | If the Ghana account is not enabled for mobile money, only card works until the channel is enabled. A later XOF switch requires an eligible Paystack entity or another gateway. |
| D-15 | **CR-01 (raised at G1):** customer accounts carry an optional profile photo and a 16-point body measurement sheet in centimetres, editable by the customer at any time and readable by the tailor at `/admin/customers`. Photo is stored as uploaded on the public disk (jpg/png/webp, 2 MB cap, no resize). | **Delivered 2026-11-05, awaiting confirmation** | Photo resizing adds an image library and work in Task 2.3; inches would need a per-customer unit and conversion; a measurement history requires a second table and changes the fitting workflow in Phase 3. |

---

## 3. Scope

### 3.1 In scope for v1 (Plan B+ — 19 working days)

- Public website in French and English
- Home page, collections, catalogue, search, product detail
- Product variants (size/colour) with per-variant stock, SKU, and optional price difference
- Customer registration, login, password reset, profile
- Wishlist
- Cart and checkout
- **Paystack payments (Ghana/GHS): card and mobile money**, with hosted checkout, server-side verification, and signed webhooks; currency is configurable for a later XOF switch
- Optional offline methods: cash on delivery and transfer confirmation (toggleable in settings)
- Order confirmation, payment receipts, order tracking, order emails
- Admin: products, variants, categories, images, orders, payments, refunds, customers, appointments, gallery, blog, pages, messages, settings
- Appointment booking with overlap prevention and admin management
- Contact form with email notification
- WhatsApp click-to-chat and Google Maps embed
- Security baseline, SEO basics, legal pages, backups
- Staging and production deployment + handover

### 3.2 Deferred to v1.1+ (not in the 19-day build)

- Customer reviews and ratings (SRS §9)
- Advanced reports and analytics
- SMS and WhatsApp Business API
- Shipment tracking
- Inventory/production management beyond stock counters
- Loyalty and referral programme
- AI recommendations and virtual fitting
- Mobile application

### 3.3 Client responsibilities

- **Day 2:** logo, product photos, brand copy, and variant data — or written approval to use placeholders.
- **Day 3:** hosting and domain details confirmed, or approval to defer deployment.
- **Day 4:** Paystack Ghana account confirmation (GHS, card + mobile money), test public/secret keys, and permission to configure the webhook URL. Also confirm GHS prices, shipping zones, and tax display.
- **Day 8:** SMTP/email account details for testing.
- **Every gate:** review and written approval within the same working day.
- **Content:** product descriptions, variant data, blog articles, and legal text supplied or approved.
- **Decisions:** answer blocking questions within 24 hours; each day of delay moves the schedule by one day.

---

## 4. Definition of Done (applies to every task)

A task is Done only when:

- [ ] Acceptance criteria are met and demonstrated.
- [ ] The relevant flow works end-to-end in the browser.
- [ ] Responsive check passed at 360, 768, 1024, and 1440 px.
- [ ] Server-side validation and authorization are applied.
- [ ] No new browser-console or server-log errors.
- [ ] Automated tests pass where the task specifies them.
- [ ] ERD/data dictionary updated if the schema changed.
- [ ] Code committed with a clear message (after the repo exists).
- [ ] Client approval is recorded in the Approval Log.

---

## 5. Environments and tooling

| Environment | Details |
|---|---|
| Local development | Windows + Laragon; PHP 8.3.28, Composer 2.10.2, MySQL 8.4.3, Node 26.2.0 — verified available |
| Staging | Same stack as production; Paystack test mode (GHS); deployed from the main branch |
| Production | PHP 8.3+, MySQL 8, HTTPS, cron, document root `src/public`; Paystack live mode (Ghana/GHS) |
| Repository | Separate git repo in `saraki/` (Task 1.1); tags at each accepted gate |
| Secrets | `.env` only; never committed; Paystack test keys until go-live |
| Backups | Daily database + uploads, 14-day retention, restore tested in Phase 6 |

**Local-first rule:** all features are built and verified locally before staging deployment.

---

## 6. Timeline and phase overview

### 6.1 Phase overview (Plan B+)

| Phase | Name | Working days | Gate | Status |
|---|---|---|---|---|
| 0 | Setup and planning | Before Day 1 | G0 (approved) + G0-final | **Done** |
| 1 | Foundation | Days 1–2 | G1 | **Done** — 1.1–1.15 delivered |
| 2 | Catalogue and variants | Days 3–5 | G2 | **Done** — 2.1–2.10 delivered |
| 3 | Cart, checkout, orders and Paystack | Days 6–11 | G3 | **Done** — 3.1–3.12 delivered |
| 4 | Appointments | Days 12–13 | G4 | **Done** — 4.1–4.7 delivered |
| 5 | Content and engagement | Days 14–15 | G5 | **Done** — 5.1–5.8 delivered |
| 6 | Hardening and UAT | Days 16–17 | G6 | Proposed |
| 7 | Deployment and handover | Days 18–19 | G7 | Proposed |
| 8 | Post-launch support and v1.1 backlog | After go-live | — | Backlog |

### 6.2 Day-by-day plan (Plan B+ — 19 working days)

| Day | Tasks | Gate |
|---|---|---|
| 1 | 1.1–1.6: repo, Laravel, environment, schema, models, seed data | — |
| 2 | 1.7–1.12: auth, roles, layouts, admin shell, i18n, tests; hosting provisioned in parallel | **G1** |
| 3 | 2.1–2.3: categories, products, images | — |
| 4 | 2.4–2.5: variant schema, variant admin; Paystack account details confirmed | — |
| 5 | 2.6–2.10: catalogue, product page with variants, home, SEO, tests | **G2** |
| 6 | 3.1–3.3: cart service, cart UI, checkout | — |
| 7 | 3.4–3.5: order creation/stock reservation, Paystack initialization | — |
| 8 | 3.6–3.7: callback/webhook verification, payment state and emails | — |
| 9 | 3.8–3.9: customer orders, admin orders and payment status | — |
| 10 | 3.10–3.11: refunds, cancellation/restock | — |
| 11 | 3.12: commerce/payment tests; staging deploy | **G3** |
| 12 | 4.1–4.4: services, hours, booking, overlap prevention | — |
| 13 | 4.5–4.7: admin appointments, notifications, tests | **G4** |
| 14 | 5.1–5.4: gallery, blog, pages, contact | — |
| 15 | 5.5–5.8: wishlist, settings, WhatsApp/Maps, tests | **G5** |
| 16 | 6.1–6.4: responsive QA, accessibility, performance, security | — |
| 17 | 6.5–6.9: errors, backups, SEO, UAT, regression | **G6** |
| 18 | 7.1–7.4: hosting/SMTP verification, cron, production preparation | — |
| 19 | 7.5–7.6: production cutover and handover | **G7** |

### 6.3 Parallel tracks

- **Hosting:** provisioned Day 2–3, staging deployed Day 11, production cutover Day 19.
- **Paystack:** account/currency confirmation Day 4; test-mode integration Days 7–8; live keys at cutover.
- **Content:** client supplies assets and copy from Day 1; content entry runs alongside development.
- **Reviews:** same-day gate reviews; the schedule moves day-for-day if reviews slip.

### 6.4 Capacity reality check

The selected Plan B+ is a **19-working-day** build with an estimated **155–165 focused agent-hours**. Available capacity is roughly 150–190 focused hours after reviews and fixes. The plan is **at capacity but feasible** only if:

- assets and variant data arrive by Day 2;
- Paystack account details and test keys arrive by Day 4;
- gate reviews happen the same day;
- hosting is confirmed by Day 3.

The largest additions are per-variant stock handling (Days 4–11) and Paystack integration (Days 7–10). If slippage occurs, the scope guard applies in this order:

1. Reduce blog depth (move advanced blog features to v1.1).
2. Reduce gallery depth.
3. Move refunds to dashboard-only (Paystack dashboard) instead of in-app refunds.
4. Extend the timeline by whole days.

Paystack card + mobile money is non-negotiable in this scope. Reviews remain deferred per SRS §9.

---

## 7. Task register

> Est. = indicative focused agent effort, not a calendar commitment.
> Status values: Proposed, Approved, In progress, In review, Done, Blocked.

### Phase 0 — Setup and planning

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 0.1 | Create project folder structure | `saraki/` with `src/`, `docs/`, `design/`, `deploy/`, `backups/`; ignore rules in place | 0.25h | **Done** (G0) |
| 0.2 | Write and approve this master plan | `plan.md` reviewed and approved at G0 | 0.5h | **Done** (G0) |
| 0.3 | Archive the approved SRS | SRS v1.0 stored in `docs/srs.md` | 0.25h | **Done** (approved 2026-11-05) |
| 0.4 | Produce the ERD (variants + payments) | Mermaid ERD in `docs/erd.md`; all v1 entities and relationships, including `product_variants`, `payments`, `payment_events`, `refunds`; reviewed with client | 2.5h | **Done** (approved 2026-11-05) |
| 0.5 | Produce the data dictionary | All tables, columns, types, constraints, and indexes in `docs/data-dictionary.md` | 2.5h | **Done** (approved 2026-11-05) |
| 0.6 | Brand design tokens and UI kit | Colours, typography, spacing, buttons, forms, cards documented in `docs/design-system.md`; applied to one static sample page (`design/sample-page.html`) | 2.5h | **Done** (approved 2026-11-05) |
| 0.7 | Confirm decisions and environments | D-01–D-14 confirmed; hosting, assets, Paystack, SMTP status recorded in `docs/decisions-and-environments.md` | 0.5h | **Done** (approved 2026-11-05) |

**Gate G0 (plan approval):** approved 2026-11-05 — plan, stack, variants, Paystack, timeline.
**Gate G0-final:** approved 2026-11-05 — ERD, data dictionary, design system, decisions and environments.

---

### Phase 1 — Foundation (Days 1–2)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 1.1 | Initialize the project repository | Separate git repo in `saraki/`; initial commit; `.gitignore` verified; no secrets tracked | 0.25h | Done — nested repo on `main`, `.env` ignored, LF enforced |
| 1.2 | Install Laravel 13 + Breeze (Blade) + Tailwind + Alpine | App boots locally; Breeze auth pages render | 0.75h | Done — Laravel 13.40, Breeze 2.4.2 Blade+Pest, Vite build passes |
| 1.3 | Configure the environment | `.env.example`, app key, MySQL connection, timezone `Africa/Accra`, locale `fr`, currency GHS (configurable), Paystack placeholders | 0.5h | Done — committed as `.env.example`; secrets stay local |
| 1.4 | Create all v1 migrations | Includes `product_variants`, `payments`, `payment_events`, `refunds`; `php artisan migrate:fresh --seed` succeeds; constraints/indexes verified | 4h | Done — 28 tables, 11 CHECKs, 24 FKs verified against MySQL 8.4 (`--seed` completes in 1.6) |
| 1.5 | Models, relationships, factories | Product ↔ variants, payments ↔ refunds/events, order items; factories generate valid data | 3h | Done — 22 models, 9 enums, 18 factories; 15 factory/model tests pass |
| 1.6 | Seeders | Admin user, 6 categories, 20 sample products with variants, 3 services, default settings | 2h | Done — 6 categories, 20 products / 139 variants, 3 services, 7 working-hour rows, 10 settings; `migrate:fresh --seed` verified and idempotent |
| 1.7 | Authentication flows | Register, login, logout, password reset, profile update; validation and rate limiting | 2h | Done — phone + locale on register/profile, validation matches column limits, login rejects deactivated accounts, register and password links throttled |
| 1.8 | Roles and authorization | Customer blocked from `/admin`; admin policies enforced on every admin route | 1.5h | Done — `admin` middleware alias + `Gate::before`; guests→login, customers→403, admins allowed (tested) |
| 1.9 | Public layout | Header, footer, navigation, flash messages, mobile menu, FR/EN switcher | 3h | Done — charcoal 80px header (skip link first, desktop nav, Alpine mobile drawer), 4-column footer collapsing to accordions, inline flash alerts, FR/EN switcher on every page; `ui/` components (button, alert, badge, stat-card, header, footer, locale-switcher, flash-messages) built to the design-system contract. **Visual pass at 360/768/1024/1440 still owed** |
| 1.10 | Admin shell and dashboard | Admin layout, sidebar, dashboard counts (orders, payments, appointments, products, messages) | 2h | Done — 260px charcoal-700 sidebar with 3px gold active bar (icon rail below `xl`), `/admin` behind `auth`+`admin`, 5 stat cards (orders, revenue this month, open appointments, active products, unread messages) + latest orders as a table that stacks into definition lists below `md`; unbuilt sections route to a labelled placeholder |
| 1.11 | Localization scaffold | `/fr` and `/en` routes, lang files, locale persistence; no hard-coded UI strings | 2h | Done — `/{fr|en}` storefront routes with `/` redirect, `SetLocale` (route → session → account, saved on profile update) with `POST /locale/{locale}`, lang files for ui/admin/statuses/auth/validation/passwords/pagination + `fr.json`; no off-token classes remain in Blade |
| 1.12 | Foundation tests | Feature tests for auth, RBAC, locale switching; all pass | 1.5h | Done — 61 tests / 228 assertions green (`php artisan test`), including 8 locale tests and 4 admin-shell tests; Breeze example tests replaced |
| 1.13 | CR-01: profile photo | Optional photo on signup, replace/remove in Profile, initials fallback in the header | 1.5h | Done — `users.avatar_path` on the public disk (jpg/png/webp, 2 MB, no resize), previous file deleted on replace and on account removal, `<x-ui.avatar>` monogram fallback, `storage:link` required locally |
| 1.14 | CR-01: measurement sheet | 16 body fields in centimetres, one row per customer, every field optional, customer-editable | 2.5h | Done — `customer_measurements` (unique `user_id`, `decimal(5,2)`), `CustomerMeasurement::BODY_FIELDS` as the single source for validation, form and display, `PATCH /profile/measurements` upsert, grouped FR/EN form with how-to-measure hints, `centimetres()` helper (fr `159,0 cm` / en `159.0 cm`) |
| 1.15 | CR-01: tailor view + tests | Admin customers list with search and a read-only sheet per customer; feature tests | 2h | Done — `/admin/customers` behind `auth`+`admin` (admins excluded from the list, admin ids 404), nav entry and icon, 21 new tests; suite at 82 tests / 300 assertions. **Visual pass at 360/768/1024/1440 still owed** |

**Gate G1:** auth, roles, layout, admin shell, and FR/EN scaffolding demonstrated. CR-01 (D-15) delivered on top of 1.12 and included in this gate.

---

### Phase 2 — Catalogue and variants (Days 3–5)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 2.1 | Categories CRUD | Create, edit, delete, reorder, activate/deactivate; FR/EN names and slugs | 2h | Done — `/admin/catalogue/categories` behind `auth`+`admin`: slug generated and kept unique, parent cycle (self or own descendant) refused, the list renumbered 1..n on move, delete blocked while products still point at it; 9 tests |
| 2.2 | Products CRUD | FR/EN name and description, base price, base SKU, status, featured flag; server-side validation | 4h | Done — one form shared by create and edit; price accepts `1 250,50`, SKU is upper-cased and unique, category required; list filters by keyword/SKU, collection and status; 13 tests |
| 2.3 | Product images | Multiple upload, reorder, delete, thumbnails; file type/size validation | 2.5h | Done — multi-upload (jpg/png/webp, 4 MB each), first photo becomes the main photo, FR/EN alt text, position field, delete removes the file and promotes the next photo; previews are scaled by the browser, no derivative thumbnail files; 8 tests |
| 2.4 | Variant schema and models | `product_variants` with size, colour, SKU, stock, optional price difference; unique SKU and variant combination; relationships and factories | 2h | Done in Phase 1; verified here and extended with `scopeActive`, `isDefault()`, and `priceFrom()` / `totalStock()` / `isSoldOut()` on the product |
| 2.5 | Variant admin | Create/edit/delete variants per product; per-variant stock and SKU; validation; optional bulk generation of size/colour combinations | 3h | Done — single variant, one bulk grid that saves every row (and deletes the rows ticked for removal), and a size × colour generator that skips combinations that already exist; duplicates refused case-insensitively; 9 tests |
| 2.6 | Public catalogue | Grid, category filter, search, pagination; search results under 2 seconds | 3h | Done — `/{locale}/catalogue` grid of 12, collection filter, search, four sort orders, empty state, `noindex` on search results, 404 for an unknown or hidden collection; 11 tests |
| 2.7 | Product detail with variant selector | Image gallery, variant selector, per-variant stock state, price update, related products | 3h | Done — gallery with thumbnails, selector driving price/stock/SKU through Alpine, sold-out badge, breadcrumbs, related pieces, `?variant=` preselects; **add to cart renders disabled until 3.1–3.2**; 10 tests |
| 2.8 | Home page | Hero, featured products, collections, brand story, calls to action | 3h | Done — hero, signature shelf (falls back to the newest pieces when nothing is flagged), six collections with published counts, new-in shelf, brand story, calls to action; 8 tests |
| 2.9 | Catalogue SEO | Slugs, meta titles/descriptions, Open Graph, canonical, hreflang | 1.5h | Done — `<x-ui.seo>` renders description, canonical, `hreflang` fr/en/x-default, Open Graph and Twitter cards per page, `og:type=product` and `og:image` on product pages; 3 tests |
| 2.10 | Catalogue and variant tests | Admin CRUD, variant stock, search, image upload, permissions | 2.5h | Done — 73 new tests (suite at 155 tests / 552 assertions), all green |

**Gate G2:** admin can publish a product with variants that appears correctly in the public catalogue, search, and product page.

Gate notes:

- **Search uses `LIKE`, not the `FULLTEXT` index.** InnoDB only adds a row to its full-text index when the writing transaction commits, so a piece stayed invisible to `MATCH … AGAINST` for the rest of the request that created it. At boutique scale a `LIKE` scan across the four text columns is immediate and always agrees with what the shop holds. The `FULLTEXT` index stays in the schema.
- **Add to cart is deliberately inert** on the product page (disabled button plus "Cart opens in Phase 3"), because the cart itself is 3.1–3.2.
- **The seeder ships no product photos**, so a freshly seeded shop shows the "no photo yet" placeholder everywhere. Gate G2 is demonstrated by uploading a photo through `/admin/catalogue/products/{id}/images`.
- **Visual pass done at a narrow viewport only** (532 px, no horizontal overflow on home, catalogue, product page, search, or the five admin catalogue screens). The 360/768/1024/1440 pixel check is still owed, matching the note on 1.15.

---

### Phase 3 — Cart, checkout, orders and Paystack (Days 6–11)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 3.1 | Cart service (variant-aware) | Add, update, remove by variant; stock validation; session/DB persistence | 2.5h | Done — `App\Services\CartService`: a database basket keyed by session for guests and by account once signed in; add/update/remove keep the shelf as the ceiling; `soldOutLines()` warns before the till; `mergeGuestCart()` hands the guest lines over on login, clamped to stock; 12 tests |
| 3.2 | Cart UI | Cart page and mini-cart; variant labels; quantity controls; empty state | 2h | Done — `/{locale}/cart` with per-line quantity and remove forms, variant labels, unavailable lines flagged and checkout blocked, empty state; header cart link with a live piece count (`x-ui.cart-link`); the product page now posts the selected variant |
| 3.3 | Checkout and payment method | Login gate; delivery/contact form; validation; GHS shipping fee; Paystack card/mobile money selection; optional COD/transfer | 3h | Done — `/{locale}/checkout` behind `auth`; one validated delivery form; the zone select drives the shipping fee, which is always looked up from settings and never taken from the browser; Paystack card/mobile money plus optional COD/transfer switched from `payment_methods` |
| 3.4 | Order creation and stock reservation | Transaction; per-variant row lock; stock reserved at order creation; price/variant snapshots; unique order number; payment pending | 3.5h | Done — `OrderService::place()` runs in one transaction: variants are locked with `lockForUpdate()` in id order, stock is decremented at creation, product name/variant label/unit price are snapshotted on the line, and the readable `SRK-2026-000001` number is written with `forceFill()` because status is not mass-assignable |
| 3.5 | Paystack initialization | Server-side initialize transaction; reference and metadata; amount ×100 (pesewas for GHS; XOF later via config); channels card + mobile_money; hosted checkout redirect; keys from `.env` | 3h | Done — `PaystackGateway::initialize()` posts amount ×100 in pesewas, currency, our reference, order metadata, the configured channels and the callback URL, then the shopper is sent to the hosted checkout with `redirect()->away()`; keys come from `.env`, and without a secret key the option is hidden rather than broken |
| 3.6 | Paystack callback and webhook | Server-side verification; HMAC SHA512 signature check; idempotency by event ID/reference; amount, currency, and reference validation; CSRF exclusion | 3h | Done — the callback re-reads the transaction from Paystack before trusting the query string; `POST /webhooks/paystack` checks HMAC SHA512 with `hash_equals`, stores every event under a unique event id (a replay is acknowledged without repeating the work), compares reference, amount and currency, and is exempt from CSRF in `bootstrap/app.php` |
| 3.7 | Payment state and notifications | Payment status on order; success/failure emails; retry link; expired unpaid orders released and restocked by scheduler | 3h | Done — the order carries `payment_status`; receipt and failure emails go out in the customer's locale; the order page offers a retry while the reservation stands; `orders:release-expired` runs every five minutes, abandons stale attempts, closes the order and restocks |
| 3.8 | Customer orders | Order list, detail, tracking timeline including payment state | 2.5h | Done — `/{locale}/orders` list and detail with a five-step timeline, items, totals, delivery card and payment attempts; an order is a 404 to anyone but the account that placed it |
| 3.9 | Admin orders and payments | List/filter/detail; order status transitions; payment status and gateway reference; manual reconciliation | 3h | Done — admin order book with status/payment/keyword filters and counters; detail screen with items, payments, refunds and history; only the next legal transitions are offered and anything else is refused; cash and transfer are reconciled into an offline payment record |
| 3.10 | Refunds | Admin-initiated full/partial refund via Paystack; refund record; payment/order status update; idempotent | 2h | Done — the refund is recorded before the gateway is called, Paystack refunds go through the API, offline payments are settled by hand, the order moves to partially refunded/refunded, and the same amount still pending is not sent twice |
| 3.11 | Cancellation and restock | Restock the correct variant on cancellation/expiry; no edits after completion | 1.5h | Done — cancellation and expiry credit each ordered variant once, guarded by `restocked_at`; a delivered order cannot be cancelled; refunds never restock |
| 3.12 | Commerce and payment tests | Cart, checkout, stock reservation/concurrency, webhook signature/idempotency, payment and refund states | 4h | Done — 60 new tests covering the basket, checkout and reservation, gateway failure, webhook signature/idempotency/amount checks, the callback, customer tracking, admin transitions, reconciliation, refunds and expiry; suite at 215 tests / 777 assertions, all green |

**Gate G3:** a complete order can be paid by card or mobile money through Paystack, verified by webhook, tracked, refunded, and cancelled with correct per-variant stock behaviour.

Gate notes:

- **Checkout needs an account.** A visitor can fill a basket, but signing in is required before an order exists; the guest basket is merged onto the account on login.
- **An online order only becomes `confirmed` once the payment is verified.** Cash on delivery is confirmed immediately and its money is reconciled by an admin later.
- **Webhooks confirm before they trust.** Every `charge.success` is re-verified against Paystack; the signed payload is only used when verification is unavailable, and the amount, currency and reference must still match.
- **Guest baskets cannot be driven across two test requests.** The array session driver used in tests does not carry the session cookie between calls, so multi-request guest flows are covered with a signed-in shopper and the guest path through `CartService` directly.
- **The Paystack secret key is not in `phpunit.xml`.** Tests set one with `enable_paystack()` and fake the API through `fake_paystack()`; nothing in the suite reaches the network.
- **Visual pass owed.** The cart, checkout, order, and admin order screens have not yet been reviewed at 360, 768, 1024 and 1440 px, matching the note on Phase 2.

---

### Phase 4 — Appointments (Days 12–13)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 4.1 | Services CRUD | FR/EN name, duration, active flag | 1.5h | Done — the admin keeps a service book (`/admin/services`) with FR and EN names, a 15–480 minute duration and an active flag; the storefront filters on `is_active`, and a service that already carries bookings is hidden rather than deleted because its foreign key is RESTRICT |
| 4.2 | Working hours and blocked dates | Admin manages weekly hours and date exceptions | 2h | Done — `/admin/schedule` edits the seven weekly rows (a closed day keeps both times null, matching the CHECK constraint) and blocks single dates with a reason; availability reads both |
| 4.3 | Booking flow | Service → date → available slots → customer details; login required | 3h | Done — `/{locale}/appointments` lists the services, the atelier hours and the customer's own bookings; `/{locale}/appointments/book` walks service → date → free slots → notes behind `auth`, and a hand-typed `?date=` is clamped back into the booking window |
| 4.4 | Overlap prevention | Availability calculation and transaction/lock protection; double-booking impossible under concurrent requests | 3h | Done — `Availability` lays each service's slots on its own duration grid inside the opening hours, minus closures and minus pending/approved/rescheduled bookings; `BookingService` writes inside a transaction that takes `lockForUpdate()` over the requested day, re-checks availability while holding it, then inserts. Rescheduling runs the same check with the moved booking excluded from its own clash test |
| 4.5 | Admin appointments | List and calendar views; approve, reschedule, cancel | 3h | Done — appointment book with status/date/period/keyword filters and today/open/pending counters, a Monday-first month planner marking open, closed and blocked days, and a detail screen that offers only the legal transitions; a reschedule asks for the new date and time |
| 4.6 | Appointment notifications | Customer and admin emails; status-change notifications | 1.5h | Done — request and status emails go to the customer in their own locale with an admin copy to `STORE_ADMIN_EMAIL`; sending is wrapped in a try/catch that logs, so a mail failure never undoes a recorded booking |
| 4.7 | Appointment tests | Overlap, availability, permissions, cancellation | 2h | Done — 35 new tests covering the locked booking, off-grid and cross-service overlaps, back-to-back slots, closed and blocked days, horizon and lead time, cancellation and its permissions, admin approve/reschedule/cancel, services CRUD, weekly hours, closures and mail rendering; suite at 250 tests / 903 assertions, all green |

**Gate G4:** a customer can request an appointment; the admin can approve/reschedule it; no two active bookings can share a slot.

Gate notes:

- **An unanswered request already holds its slot.** Availability blocks on `pending`, `approved` and `rescheduled`; `cancelled` and `completed` release the slot again.
- **The lock is a date, not a slot.** `BookingService` locks every row of the requested day and re-runs the availability check while holding the lock, so a concurrent insert waits and then sees the first booking. The overlap test itself is `start < other_end AND end > other_start`.
- **Status is guarded.** `Appointment::$fillable` leaves `status` out, so it only moves through `BookingService::transition()`, which refuses jumps the state machine does not allow (a cancelled or completed booking is final); the admin screen offers only the legal moves.
- **Times are `TIME` values with a `datetime:H:i` cast.** Their Carbon carries today's date, so comparisons attach the booking's own date first through `Appointment::startsAt()` / `endsAt()`.
- **The admin placeholder does not swallow these routes.** `appointments` was removed from the `admin/{section}` whitelist and the real routes are registered ahead of it.
- **Lead time and horizon come from `.env`.** `APPOINTMENT_LEAD_MINUTES` (120) keeps a slot from being taken at the last minute and `APPOINTMENT_HORIZON_DAYS` (60) bounds the calendar; both are injectable so tests can pin them.
- **Visual pass owed.** The shop booking pages and the admin appointment, service and schedule screens have not yet been reviewed at 360, 768, 1024 and 1440 px, matching the note on Phases 2 and 3.

---

### Phase 5 — Content and engagement (Days 14–15)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 5.1 | Gallery | Admin CRUD; public grid and lightbox | 2h | Done — `/admin/content/gallery` keeps FR/EN titles, a category from `GalleryCategory`, a sort order, an uploaded photo, a publish toggle and delete; `/{locale}/gallery` renders the published grid, filters by category (an unknown one 404s), and opens an Alpine lightbox that walks the already-loaded photos without another request |
| 5.2 | Blog | Admin CRUD with FR/EN fields, draft/publish, cover image, author, date; public list and detail | 3h | Done — `/admin/content/posts` writes FR/EN title, excerpt and body with a unique slug, cover image, author and publish date; the list filters by keyword and status; `/{locale}/journal` lists the published articles and a draft is a 404 on its own detail page |
| 5.3 | Static pages CMS | About, shipping/returns, privacy, terms editable from admin | 2h | Done — `/admin/content/pages` creates, edits and publishes any page and `/{locale}/pages/{slug}` serves it; the five footer-linked slugs are `Page::FIXED_SLUGS`, which both the screen and `destroy()` refuse to delete |
| 5.4 | Contact module | Form validation, database save, email notification, honeypot and rate limit | 2h | Done — `/{locale}/contact` validates name/email/subject/message (20–3000 characters), stores the enquiry and mails the sender in their locale plus a copy to `STORE_ADMIN_EMAIL`; the hidden `website` honeypot gets the same success while storing nothing and the route allows five messages a minute; `/admin/messages` counts by status, searches, files each enquiry new → read → replied → archived and replies by mail |
| 5.5 | Wishlist | Add, remove, list; account page | 1.5h | Done — `/{locale}/wishlist` sits behind `auth` and lists the saved pieces with a plural count; the product page posts a save, a repeat is ignored, a piece that is no longer active is refused, and the list is private to the account; the dashboard shows the count and the account menu links to it |
| 5.6 | Settings module | Store info, WhatsApp number, map embed, shipping fees, payment method toggles, social links | 2h | Done — one `/admin/settings` screen writes the store details, repeatable delivery zones (blank rows dropped, an empty EN name falls back to FR), the payment toggles (at least one required), tax display, order expiry and social links; the `Settings` service caches one snapshot per request and is shared with every view |
| 5.7 | WhatsApp and Maps | Floating click-to-chat button; embedded showroom map | 1h | Done — `x-ui.whatsapp-button` floats on every storefront page once a number of at least eight digits is configured (`wa.me` with the message pre-filled, absent otherwise), the contact page embeds the showroom map, and the footer lists only the social profiles that carry a URL |
| 5.8 | Content tests | CRUD, contact, wishlist, pages, settings | 2h | Done — 33 new tests covering the gallery, posts, pages, the content hub, the inbox, the contact form's validation/honeypot/throttle/mail, the wishlist and settings; suite at 283 tests / 1059 assertions, all green |

**Gate G5:** admin can publish gallery, blog, and page content without code; contact and wishlist work end-to-end.

Gate notes:

- **The footer-linked pages cannot be deleted.** `Page::FIXED_SLUGS` (`about`, `shipping`, `returns`, `privacy`, `terms`) is the single list used by the admin index, the edit form and `destroy()`, which refuses with an error flash even if the request is crafted — a stray click cannot break a footer link.
- **The storefront binds on slugs, the admin on ids.** `BlogPost` and `Page` override `getRouteKeyName()`, so the admin routes declare `{post:id}` / `{page:id}` with `whereNumber` and every link and redirect passes the id explicitly: `route()` reads the model's route key, which would otherwise emit a slug into a numeric route.
- **`Settings` is one snapshot per request.** Values load once in the service, `putMany()` saves a whole form in one transaction and clears the snapshot, and the service is shared to every view as `$settings` — the footer, the WhatsApp button and the contact page always read the same values.
- **The map is accepted as a URL, never as markup.** `SettingsRequest` normalises through `Settings::embedUrl()`, which lifts the `src` out of a pasted Google iframe and refuses anything that is not plain http(s); what the validator accepts is exactly what the iframe renders.
- **The honeypot answers like a person.** A filled `website` field is validated as free text and answered with the ordinary success redirect, but nothing is stored or mailed, so a script learns nothing from the reply.
- **Visual pass owed.** The gallery, journal, page, contact and wishlist screens and the admin content, messages and settings screens have not yet been reviewed at 360, 768, 1024 and 1440 px, matching the note on Phases 2–4.

---

### Phase 6 — Hardening and UAT (Days 16–17)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 6.1 | Responsive QA | 360/768/1024/1440 px; Chrome, Firefox, Safari, Edge; defects fixed | 3h | Proposed |
| 6.2 | Accessibility pass | Contrast, labels, keyboard navigation, focus states, alt text | 2h | Proposed |
| 6.3 | Performance | WebP/thumbnails, lazy loading, caching, query review, pagination | 3h | Proposed |
| 6.4 | Security hardening | Validation audit, CSRF, XSS, upload checks, rate limits, security headers, session settings, webhook replay protection | 3h | Proposed |
| 6.5 | Error handling | 404/500/419 pages, logging, health check | 1.5h | Proposed |
| 6.6 | Backups | Daily database + uploads, 14-day retention, successful restore test | 2h | Proposed |
| 6.7 | SEO | Meta/Open Graph, sitemap, robots, canonical, hreflang | 2h | Proposed |
| 6.8 | UAT | Written UAT script, client walkthrough, bug log; GHS card and mobile money test payments | 4h | Proposed |
| 6.9 | Regression | Full test suite; critical and high defects fixed | 2h | Proposed |

**Gate G6:** UAT sign-off; no open critical or high defects; successful test payment for card and mobile money.

---

### Phase 7 — Deployment and handover (Days 18–19)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 7.1 | Provision staging and production | PHP 8.3+, MySQL 8, SSL, cron, document root `src/public`; credentials recorded securely | 2h | Proposed |
| 7.2 | Staging deployment | App runs on staging; storage link and permissions correct; Paystack test mode verified | 2h | Proposed |
| 7.3 | SMTP configuration | Test emails delivered to customer and admin inboxes | 1h | Proposed |
| 7.4 | Cron and backups | Scheduler (payment expiry, backups) running on the server | 1h | Proposed |
| 7.5 | Production cutover | DNS/SSL, live Paystack keys (Ghana/GHS), webhook URL, go-live checklist, smoke tests on all critical flows | 2h | Proposed |
| 7.6 | Handover | Admin guide, deployment runbook, credential transfer, maintenance checklist | 2h | Proposed |

**Gate G7:** production site live over HTTPS; a real card/mobile money payment succeeds; handover accepted.

---

### Phase 8 — Post-launch support and v1.1 backlog

| ID | Item | Notes | Priority |
|---|---|---|---|
| 8.1 | Reviews and ratings | Verified purchase, moderation | High |
| 8.2 | Reports and analytics | Sales, stock, appointments, traffic | Medium |
| 8.3 | SMS / WhatsApp Business API | Templates, opt-in, delivery tracking | Medium |
| 8.4 | Shipment tracking | Carrier statuses and customer notifications | Medium |
| 8.5 | Inventory and production management | Stock movements, low-stock alerts | Medium |
| 8.6 | Loyalty and referrals | Points, coupons, referral codes | Low |
| 8.7 | AI recommendations | Style suggestions based on browsing/purchase history | Low |
| 8.8 | Mobile app | API layer + React Native or Flutter | Future |

**Post-launch support:** 7 days of monitoring and bug triage after go-live, then a maintenance agreement if required.

---

## 8. Requirement traceability (SRS → tasks)

| SRS requirement | Covered by |
|---|---|
| Feature 1 — User registration | 1.7 |
| Feature 2 — User login | 1.7 |
| Feature 3 — Product catalogue | 2.1–2.9 |
| Feature 4 — Shopping cart | 3.1–3.2 |
| Feature 5 — Checkout and payment | 3.3–3.7; Paystack card + mobile money |
| Feature 6 — Appointment booking | 4.1–4.6 |
| Feature 7 — Portfolio gallery | 5.1 |
| Feature 8 — Blog module | 5.2 |
| Feature 9 — Contact module | 5.4 |
| Feature 10 — Admin dashboard | 1.10, 2.1–2.5, 3.9, 4.5, 5.1–5.6 |
| Wishlist (§2.3) | 5.5 |
| Order tracking (§2.3) | 3.8 |
| Product variants (client decision D-02) | 2.4–2.5, 2.7, 3.1, 3.4, 3.11, 3.12 |
| Paystack payments (client decision D-03) | 3.3–3.7, 3.9–3.10, 6.4, 6.8, 7.5 |
| Multilingual EN/FR | 1.11; FR/EN content fields in 2.2, 5.2, 5.3 |
| WhatsApp integration | 5.7 |
| Google Maps integration | 5.7 |
| Performance (3s load, 200 users, <2s search) | 6.3 |
| Security (passwords, HTTPS, SQLi, XSS, validation, RBAC) | 1.7, 1.8, 6.4 |
| Availability and backups (99%, daily) | 6.6, 7.4; uptime is a hosting responsibility |
| Maintainability (MVC, documented, reusable) | 1.2, 1.4, 1.11; ERD and data dictionary in Phase 0 |
| Usability and accessibility | 6.1, 6.2 |
| Reviews (§6 entity, §9 future) | Deferred, D-04 |

---

## 9. Risks and mitigations

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Brand assets or product photos arrive late | High | Launch looks empty | Build with placeholders; content entry runs in parallel; deadline for assets is Day 2 |
| Client review takes more than one day | Medium | Schedule slips day-for-day | Same-day gate reviews agreed; escalation after 24 hours |
| Hosting is shared and cannot run scheduler/backups | Medium | Deployment or backups blocked | Confirm PHP 8.3+/MySQL 8/cron by Day 3; recommend a small VPS if needed |
| Scope creep during the build | Medium | Deadline missed | Change Log + v1.1 backlog; nothing enters v1 without explicit approval |
| 19-day Plan B+ is at capacity | Medium | Incomplete polish | Scope guard in §6.4; defer blog/gallery depth before extending |
| Paystack Ghana account lacks the mobile money channel | Medium | Card-only launch | Confirm channel enablement by Day 4 (D-14); if unavailable, launch card-only or switch account after approval |
| Later GHS → XOF currency switch is disruptive | Medium | Price and shipping rework | Currency, locale, and shipping fees are settings; price import prepared; Paystack XOF entity confirmed before the switch |
| Paystack webhook spoofing or replay | Low | False paid orders | HMAC SHA512 signature check, idempotency, server-side verification, amount/currency checks (3.6) |
| Payment failure leaves stock reserved | Medium | Overselling/underselling | Reserve at order creation; scheduled release/restock for expired unpaid orders (3.7/3.11) |
| Refund mismatch | Low | Financial discrepancy | Refund records, idempotency, total ≤ payment, reconciliation view (3.10) |
| Variant data is incomplete or inconsistent | High | Catalogue errors | Variant schema and admin validation in 2.4–2.5; seed realistic combinations; client supplies variant data by Day 2 |
| Email deliverability (SMTP) | Medium | Notifications go to spam | SPF/DKIM guidance at deployment; test with real inboxes |
| Concurrent checkout oversells variant stock | Low | Wrong inventory | Transaction + per-variant row lock + tests in 3.4 and 3.12 |
| Appointment double-booking | Low | Operational conflict | Transaction/lock + unique slot rule + tests in 4.4 and 4.7 |
| Data loss | Low | Business impact | Daily backups, restore test in 6.6, off-site copy recommended |
| Security vulnerability introduced | Medium | Reputation/legal | Laravel defaults, validation audit, rate limits, security checklist in 6.4 |

---

## 10. Change control

| Date | Version | Change | Requested by | Approved by |
|---|---|---|---|---|
| 2026-11-05 | 0.1 | Initial master plan | Qoder / SahelSoft | Pending |
| 2026-11-05 | 0.2 | G0 approved; Laravel 11 confirmed; variants included; Plan B (15 working days) selected | Client | Rafiatou Malam Ali (PM) |
| 2026-11-05 | 0.3 | Paystack payments added (card + mobile money); timeline extended to 19 working days | Client | Rafiatou Malam Ali (PM) |
| 2026-11-05 | 0.4 | Market set to Ghana/GHS for v1; currency and locale made configurable for a later XOF switch | Client | Rafiatou Malam Ali (PM) |
| 2026-11-05 | 0.5 | G0-final approved. D-01 changed from Laravel 11 to **Laravel 13** (PHP 8.3+): Composer blocks all 11.x releases due to unpatched security advisories and 11.x is out of security support. No scope or schedule impact. Task 1.3 timezone/currency corrected to Africa/Accra / GHS. | Qoder (security finding) | Rafiatou Malam Ali (PM) |
| 2026-11-08 | 0.6 | Status update only: Phases 1–5 built and documented, gates G1–G5 awaiting sign-off. No scope, cost, or schedule change. | Qoder | Pending |
| 2026-11-08 | 0.7 | Status update only: Phases 1–5 marked **done** — tasks 1.9 and 1.10 moved from In review to Done, and a Status column added to the §6.1 phase overview. Gates G1–G5 remain awaiting client sign-off. No scope, cost, or schedule change. | Qoder | Pending |

Rules:

- Changes after a gate are logged here with schedule and cost impact.
- A change is only implemented after written approval.
- Unapproved ideas go to the Phase 8 backlog.

---

## 11. Approval log

| Gate | Scope approved | Date | Approved by |
|---|---|---|---|
| G0 | Plan, stack (Laravel 11), variants included, Paystack included, Plan B+ timeline | 2026-11-05 | Rafiatou Malam Ali (PM) |
| G0-final | ERD, data dictionary, design tokens, environments | 2026-11-05 | Rafiatou Malam Ali (PM) |
| G1 | Foundation | — | Pending |
| G2 | Catalogue and variants | — | Pending |
| G3 | Cart, checkout, orders and Paystack | — | Pending |
| G4 | Appointments | — | Pending |
| G5 | Content and engagement | — | Pending |
| G6 | Hardening and UAT | — | Pending |
| G7 | Deployment and handover | — | Pending |

---

## 12. Immediate next actions

1. **Phases 1–5 are done**; the full suite passes (283 tests / 1059 assertions).
2. **Gates G1–G5 await client sign-off**; the visual pass at 360/768/1024/1440 px is still owed across every phase.
3. **Phase 6 (Hardening and UAT) is next**, starting with Task 6.1; it does not start until G5 is signed.
