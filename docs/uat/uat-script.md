# SARAKI — User Acceptance Test Script

**Version:** 1.0 · **Date:** 2026-11-13 · **Environment:** staging / client review
**Participants:** client (tailor/studio owner), developer (facilitator)
**Prerequisites:** seeded catalogue, admin account, Paystack **test** keys, SMTP configured (or mail logged locally), at least one appointment service and opening hours configured.

Each step lists the action, the expected result, and a pass/fail checkbox. Record defects in `bug-log.md` with the step ID.

---

## A. Storefront — browsing and language

| ID | Action | Expected result | Pass |
|---|---|---|---|
| A1 | Open `/` | Redirects to `/fr`; French homepage renders with hero, featured pieces, collections | ☐ |
| A2 | Switch language to English via the header switcher | All UI text switches to English; URL stays under `/en`; choice remembered on next visit | ☐ |
| A3 | Browse `/fr/catalogue` | Product grid, category chips, sort and search controls visible; 12 per page | ☐ |
| A4 | Filter by a category, then search a product name | Only matching products shown; search with no match shows empty state, not an error | ☐ |
| A5 | Open a product page | Photos, description FR/EN, size/colour variants, price, stock status; choosing a variant updates price/SKU/stock without reload | ☐ |
| A6 | Open `/fr/gallery`, `/fr/journal`, a journal article, `/fr/contact`, and an editable page (e.g. `/fr/pages/about`) | All render with correct locale content; journal article shows author/date | ☐ |

## B. Cart and checkout

| ID | Action | Expected result | Pass |
|---|---|---|---|
| B1 | Add a product to cart as a **guest** | Cart badge updates; `/fr/cart` shows the line with correct price and delivery-fee preview | ☐ |
| B2 | Register a customer account, then log in | Guest cart merges into the account cart; badge count is preserved | ☐ |
| B3 | Change quantity / remove a line in cart | Totals (items + delivery zone fee) recalculate correctly in GHS | ☐ |
| B4 | Proceed to checkout, fill delivery details, choose **COD** or **bank transfer** | Order is placed, confirmation shown, order appears under `/fr/orders` with correct status and total | ☐ |
| B5 | Repeat checkout choosing **Paystack — card** with a GHS test card (Paystack test keys) | Redirected to Paystack hosted checkout; on success the order shows **Paid** and stock is decremented once | ☐ |
| B6 | Repeat choosing **Paystack — Mobile Money** (MTN/Vodafone test number) | Same as B5 via the MoMo prompt; order marked paid after verification | ☐ |
| B7 | Abandon a Paystack checkout (close the tab) and return | Order stays unpaid; stock reservation expires and is released after the configured expiry window | ☐ |
| B8 | Attempt to order more than available stock | Blocked with a clear stock message; no oversell | ☐ |

## C. Appointments

| ID | Action | Expected result | Pass |
|---|---|---|---|
| C1 | Open `/fr/appointments` | Services, studio info, WhatsApp/Maps links render | ☐ |
| C2 | As a guest, try `/fr/appointments/book` | Redirected to login/register | ☐ |
| C3 | Book a consultation/measurement slot inside opening hours | Confirmation shown; slot disappears from availability for a second booking attempt | ☐ |
| C4 | Try booking inside lead time, outside opening hours, or on a blocked date | Rejected with a clear message | ☐ |
| C5 | Cancel the appointment from the account area | Status updates; slot becomes bookable again | ☐ |

## D. Customer account

| ID | Action | Expected result | Pass |
|---|---|---|---|
| D1 | Register, log in, log out | Session works in both locales; logout returns to storefront | ☐ |
| D2 | Update profile (name, email, phone) and upload a photo | Saved; photo renders as WebP thumbnail in header/account | ☐ |
| D3 | Fill in the measurement sheet (bust, waist, etc.) | Saved; values shown formatted in centimetres | ☐ |
| D4 | Add/remove wishlist items | Wishlist page lists products; remove works; count badge correct | ☐ |
| D5 | View order history and an order detail | Status timeline and totals correct; payment state matches B5/B6 | ☐ |

## E. Contact and content

| ID | Action | Expected result | Pass |
|---|---|---|---|
| E1 | Submit the contact form with valid details | Success message; message appears in `/admin/messages` | ☐ |
| E2 | Submit the contact form rapidly 6+ times | Rate-limited (429 or friendly error), inbox not flooded | ☐ |

## F. Admin panel

| ID | Action | Expected result | Pass |
|---|---|---|---|
| F1 | Log in as admin → `/admin/dashboard` | Dashboard shows orders, appointments, revenue figures | ☐ |
| F2 | Products: create/edit a product with photos and variants (sizes × colours) | Saved; photos stored as WebP + thumbnail; variants generate stock/SKU correctly; product appears on storefront | ☐ |
| F3 | Orders: advance an order through statuses; reconcile a Paystack payment; issue a refund | Status history recorded; stock restocked on cancel per rules; refund logged | ☐ |
| F4 | Appointments: view calendar, update a booking status | Calendar reflects the change; customer sees updated status | ☐ |
| F5 | Content: publish a journal post and an editable page; add a gallery item | All appear on the storefront in both locales | ☐ |
| F6 | Settings: update store details, delivery zones, payment methods, opening hours | Storefront reflects changes (delivery fee, WhatsApp number, hours) | ☐ |
| F7 | As a **customer** (non-admin), try `/admin/dashboard` | 403 — admin area is not accessible | ☐ |

## G. Non-functional spot checks

| ID | Action | Expected result | Pass |
|---|---|---|---|
| G1 | Resize browser to 360 px and 1440 px on home/catalogue/product/checkout | No horizontal scroll; layout intact (full matrix already verified in Task 6.1) | ☐ |
| G2 | Navigate key flows with keyboard only | Focus visible, menus/dialogs operable, skip link works (verified in Task 6.2) | ☐ |
| G3 | Check `/sitemap.xml` and `/robots.txt` | Valid XML; robots disallows private paths and lists the sitemap | ☐ |
| G4 | Simulate a server error page (e.g. stop DB briefly) | Branded localized error page renders, not a blank screen | ☐ |

---

## Sign-off

| Role | Name | Signature | Date |
|---|---|---|---|
| Client | | | |
| Developer | | | |

**Gate G6 criteria:** all critical steps pass; no open critical/high defects in `bug-log.md`; B5 and B6 completed with Paystack test keys on staging.
