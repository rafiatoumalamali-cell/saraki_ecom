# SARAKI — Admin Guide

For the atelier team running the shop day to day. Everything lives under `/admin`
and requires an **admin** account (created by the developer or an existing admin
under Customers is not possible — ask the developer to promote an account).

The admin area is available in French and English; it follows the same language
switcher as the storefront.

---

## 1. Dashboard (`/admin/dashboard`)

Snapshot of the store: orders, revenue this month, upcoming appointments,
active products, unread messages. A **warning banner** appears here if no
payment method is usable at checkout — check Settings → Payment methods first.

## 2. Catalogue (`/admin/catalogue`)

### Products

- **Create/edit** a product: French **and** English name, description, category,
  base price and SKU, active/featured flags.
- **Photos** (`products → images`): upload JPG/PNG/WebP up to 6 MB. The app
  converts every upload to WebP and generates a card thumbnail automatically —
  nothing else to do. Drag to reorder, mark one photo primary.
- **Variants** (`products → variants`): sizes × colours with individual SKU,
  stock quantity, active flag and optional price delta. Use **generate** to
  create the full size×colour matrix from two lists, then adjust stock per row.
  A product with zero stock everywhere shows as sold out.

### Categories

Ordered list shown as chips on the catalogue page. Rename in both languages,
reorder with the arrows, deactivate instead of deleting when products still
reference the category.

## 3. Orders (`/admin/orders`)

Each order shows items, delivery details, payment attempts and status history.

- **Statuses**: Pending → Confirmed → In Production → Ready → Delivered;
  Cancelled releases reserved stock exactly once.
- **Payments**: Paystack card/mobile-money payments confirm themselves via the
  webhook + callback. For cash on delivery or bank transfer, use **reconcile**
  once the money arrives, and **refund** for Paystack payments that must be
  returned.
- Unpaid Paystack orders release their stock automatically after the configured
  payment window (Settings → order expiry).

## 4. Appointments (`/admin/appointments`)

- **Requests**: confirm, reschedule-note or cancel each booking; the customer
  sees the status change.
- **Calendar**: month view of all bookings at a glance.
- **Services** (`/admin/services`): consultation, measurement, fitting — name in
  both languages, duration, price, active flag.
- **Schedule** (`/admin/schedule`): weekly opening hours, blocked dates
  (holidays, days off), lead time and how far ahead customers may book.

## 5. Customers (`/admin/customers`)

Search by name/email/phone. Each profile shows contact details and the
**measurement sheet** the tailor cuts from — incomplete sheets are flagged so
staff can chase them before a fitting.

## 6. Content (`/admin/content`)

- **Gallery**: portfolio pieces with category, caption FR/EN, cover image.
- **Journal**: blog posts — draft until published; set `published_at` for the
  date shown to readers. Cover images are stored as WebP + thumbnail.
- **Pages**: editable CMS pages (`about`, `shipping`, `returns`, `privacy`,
  `terms` are fixed slugs linked from the footer — do not rename them).

## 7. Messages (`/admin/messages`)

Contact-form enquiries. Mark as handled; delete only spam.

## 8. Settings (`/admin/settings`)

One screen, saved atomically:

- **Store**: name, contact email/phone/address (also feeds the Organization
  structured data), WhatsApp number, Google Maps embed.
- **Delivery**: named zones with fee + delivery days — these drive checkout
  pricing. Leave the last row blank to keep it ignored.
- **Payment methods**: Paystack, cash on delivery, bank transfer. At least one
  must stay on. **If Paystack is ticked but `PAYSTACK_SECRET_KEY` is missing,
  a warning names the variable** — the option stays hidden at checkout until
  the key is configured.
- **Commerce**: order payment window, tax notice.
- **Social**: profile URLs shown in the footer.

---

## Daily routine (5 minutes)

1. Dashboard → unread messages and today's appointments.
2. Orders → advance confirmed orders, reconcile COD/transfer payments.
3. Content → publish scheduled journal posts / gallery additions.
