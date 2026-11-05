# SARAKI Digital Platform — Master Build Plan

| Field | Value |
|---|---|
| Project | SARAKI Digital Platform |
| Client | SARAKI |
| Prepared by | Qoder for SahelSoft |
| Source requirements | SRS v1.0 (July 2026, SahelSoft) |
| Plan version | 0.5 — Laravel 13; Phase 1 in progress |
| Date | 2026-11-05 |
| Target | Production-ready v1 in 19 working days (~4 weeks), variants + Paystack (Ghana/GHS) included |
| Status | G0 and G0-final approved; Phase 1 in progress |

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

| Phase | Name | Working days | Gate |
|---|---|---|---|
| 0 | Setup and planning | Before Day 1 | G0 (approved) + G0-final |
| 1 | Foundation | Days 1–2 | G1 |
| 2 | Catalogue and variants | Days 3–5 | G2 |
| 3 | Cart, checkout, orders and Paystack | Days 6–11 | G3 |
| 4 | Appointments | Days 12–13 | G4 |
| 5 | Content and engagement | Days 14–15 | G5 |
| 6 | Hardening and UAT | Days 16–17 | G6 |
| 7 | Deployment and handover | Days 18–19 | G7 |
| 8 | Post-launch support and v1.1 backlog | After go-live | — |

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
| 1.1 | Initialize the project repository | Separate git repo in `saraki/`; initial commit; `.gitignore` verified; no secrets tracked | 0.25h | Proposed |
| 1.2 | Install Laravel 13 + Breeze (Blade) + Tailwind + Alpine | App boots locally; Breeze auth pages render | 0.75h | Proposed |
| 1.3 | Configure the environment | `.env.example`, app key, MySQL connection, timezone `Africa/Accra`, locale `fr`, currency GHS (configurable), Paystack placeholders | 0.5h | Proposed |
| 1.4 | Create all v1 migrations | Includes `product_variants`, `payments`, `payment_events`, `refunds`; `php artisan migrate:fresh --seed` succeeds; constraints/indexes verified | 4h | Proposed |
| 1.5 | Models, relationships, factories | Product ↔ variants, payments ↔ refunds/events, order items; factories generate valid data | 3h | Proposed |
| 1.6 | Seeders | Admin user, 6 categories, 20 sample products with variants, 3 services, default settings | 2h | Proposed |
| 1.7 | Authentication flows | Register, login, logout, password reset, profile update; validation and rate limiting | 2h | Proposed |
| 1.8 | Roles and authorization | Customer blocked from `/admin`; admin policies enforced on every admin route | 1.5h | Proposed |
| 1.9 | Public layout | Header, footer, navigation, flash messages, mobile menu, FR/EN switcher | 3h | Proposed |
| 1.10 | Admin shell and dashboard | Admin layout, sidebar, dashboard counts (orders, payments, appointments, products, messages) | 2h | Proposed |
| 1.11 | Localization scaffold | `/fr` and `/en` routes, lang files, locale persistence; no hard-coded UI strings | 2h | Proposed |
| 1.12 | Foundation tests | Feature tests for auth, RBAC, locale switching; all pass | 1.5h | Proposed |

**Gate G1:** auth, roles, layout, admin shell, and FR/EN scaffolding demonstrated.

---

### Phase 2 — Catalogue and variants (Days 3–5)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 2.1 | Categories CRUD | Create, edit, delete, reorder, activate/deactivate; FR/EN names and slugs | 2h | Proposed |
| 2.2 | Products CRUD | FR/EN name and description, base price, base SKU, status, featured flag; server-side validation | 4h | Proposed |
| 2.3 | Product images | Multiple upload, reorder, delete, thumbnails; file type/size validation | 2.5h | Proposed |
| 2.4 | Variant schema and models | `product_variants` with size, colour, SKU, stock, optional price difference; unique SKU and variant combination; relationships and factories | 2h | Proposed |
| 2.5 | Variant admin | Create/edit/delete variants per product; per-variant stock and SKU; validation; optional bulk generation of size/colour combinations | 3h | Proposed |
| 2.6 | Public catalogue | Grid, category filter, search, pagination; search results under 2 seconds | 3h | Proposed |
| 2.7 | Product detail with variant selector | Image gallery, variant selector, per-variant stock state, price update, add to cart, related products | 3h | Proposed |
| 2.8 | Home page | Hero, featured products, collections, brand story, calls to action | 3h | Proposed |
| 2.9 | Catalogue SEO | Slugs, meta titles/descriptions, Open Graph, canonical, hreflang | 1.5h | Proposed |
| 2.10 | Catalogue and variant tests | Admin CRUD, variant stock, search, image upload, permissions | 2.5h | Proposed |

**Gate G2:** admin can publish a product with variants that appears correctly in the public catalogue, search, and product page.

---

### Phase 3 — Cart, checkout, orders and Paystack (Days 6–11)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 3.1 | Cart service (variant-aware) | Add, update, remove by variant; stock validation; session/DB persistence | 2.5h | Proposed |
| 3.2 | Cart UI | Cart page and mini-cart; variant labels; quantity controls; empty state | 2h | Proposed |
| 3.3 | Checkout and payment method | Login gate; delivery/contact form; validation; GHS shipping fee; Paystack card/mobile money selection; optional COD/transfer | 3h | Proposed |
| 3.4 | Order creation and stock reservation | Transaction; per-variant row lock; stock reserved at order creation; price/variant snapshots; unique order number; payment pending | 3.5h | Proposed |
| 3.5 | Paystack initialization | Server-side initialize transaction; reference and metadata; amount ×100 (pesewas for GHS; XOF later via config); channels card + mobile_money; hosted checkout redirect; keys from `.env` | 3h | Proposed |
| 3.6 | Paystack callback and webhook | Server-side verification; HMAC SHA512 signature check; idempotency by event ID/reference; amount, currency, and reference validation; CSRF exclusion | 3h | Proposed |
| 3.7 | Payment state and notifications | Payment status on order; success/failure emails; retry link; expired unpaid orders released and restocked by scheduler | 3h | Proposed |
| 3.8 | Customer orders | Order list, detail, tracking timeline including payment state | 2.5h | Proposed |
| 3.9 | Admin orders and payments | List/filter/detail; order status transitions; payment status and gateway reference; manual reconciliation | 3h | Proposed |
| 3.10 | Refunds | Admin-initiated full/partial refund via Paystack; refund record; payment/order status update; idempotent | 2h | Proposed |
| 3.11 | Cancellation and restock | Restock the correct variant on cancellation/expiry; no edits after completion | 1.5h | Proposed |
| 3.12 | Commerce and payment tests | Cart, checkout, stock reservation/concurrency, webhook signature/idempotency, payment and refund states | 4h | Proposed |

**Gate G3:** a complete order can be paid by card or mobile money through Paystack, verified by webhook, tracked, refunded, and cancelled with correct per-variant stock behaviour.

---

### Phase 4 — Appointments (Days 12–13)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 4.1 | Services CRUD | FR/EN name, duration, active flag | 1.5h | Proposed |
| 4.2 | Working hours and blocked dates | Admin manages weekly hours and date exceptions | 2h | Proposed |
| 4.3 | Booking flow | Service → date → available slots → customer details; login required | 3h | Proposed |
| 4.4 | Overlap prevention | Availability calculation and transaction/lock protection; double-booking impossible under concurrent requests | 3h | Proposed |
| 4.5 | Admin appointments | List and calendar views; approve, reschedule, cancel | 3h | Proposed |
| 4.6 | Appointment notifications | Customer and admin emails; status-change notifications | 1.5h | Proposed |
| 4.7 | Appointment tests | Overlap, availability, permissions, cancellation | 2h | Proposed |

**Gate G4:** a customer can request an appointment; the admin can approve/reschedule it; no two active bookings can share a slot.

---

### Phase 5 — Content and engagement (Days 14–15)

| ID | Task | Deliverable and acceptance criteria | Est. | Status |
|---|---|---|---|---|
| 5.1 | Gallery | Admin CRUD; public grid and lightbox | 2h | Proposed |
| 5.2 | Blog | Admin CRUD with FR/EN fields, draft/publish, cover image, author, date; public list and detail | 3h | Proposed |
| 5.3 | Static pages CMS | About, shipping/returns, privacy, terms editable from admin | 2h | Proposed |
| 5.4 | Contact module | Form validation, database save, email notification, honeypot and rate limit | 2h | Proposed |
| 5.5 | Wishlist | Add, remove, list; account page | 1.5h | Proposed |
| 5.6 | Settings module | Store info, WhatsApp number, map embed, shipping fees, payment method toggles, social links | 2h | Proposed |
| 5.7 | WhatsApp and Maps | Floating click-to-chat button; embedded showroom map | 1h | Proposed |
| 5.8 | Content tests | CRUD, contact, wishlist, pages, settings | 2h | Proposed |

**Gate G5:** admin can publish gallery, blog, and page content without code; contact and wishlist work end-to-end.

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

1. **Phase 0 is complete; G0-final approved 2026-11-05.**
2. **Phase 1 (Foundation) is in progress**, starting with Task 1.1.
3. Phase 1 ends at **Gate G1**; Phase 2 does not start until G1 is signed.
