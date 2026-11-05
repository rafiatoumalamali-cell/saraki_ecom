# SARAKI — Design System and UI Kit

| Field | Value |
|---|---|
| Project | SARAKI Digital Platform |
| Document | Design tokens, component rules, and UI kit |
| Version | 0.1 |
| Date | 2026-11-05 |
| Source | SRS v1.0 §2 (branding), §3 (features) |
| Applies to | Storefront, customer account, admin panel, transactional emails |
| Status | In review (Task 0.6) |

This document is the single source of truth for colour, type, spacing, and component behaviour. Implementation uses **Tailwind CSS** with the token values below mapped into `tailwind.config.js` and CSS custom properties. No page should introduce a colour, font size, or spacing value that is not defined here.

---

## 1. Brand direction

SARAKI is a luxury African fashion house. The interface should feel like a printed lookbook: generous white space, restrained colour, high-contrast photography, and confident typography.

**Principles**

1. **Image first.** Product photography carries the brand. UI chrome stays quiet.
2. **Gold is a signal, not a surface.** Gold marks actions, prices, and emphasis — never large text areas on light backgrounds.
3. **Calm density.** Large spacing on marketing pages, tighter but never cramped spacing in admin tables.
4. **One primary action per screen.** Everything else is a secondary or text button.
5. **Bilingual by default.** French is the default locale; all labels must survive ~30% length growth in English without breaking layout.

---

## 2. Colour

### 2.1 Brand palette

| Token | Hex | Usage |
|---|---|---|
| `brand.charcoal` | `#0A0A0A` | Primary text, header/footer background, primary button surface |
| `brand.charcoal-800` | `#141414` | Hover state for charcoal surfaces |
| `brand.charcoal-700` | `#1F1F1F` | Elevated dark surfaces, admin sidebar |
| `brand.beige` | `#F5F1E8` | Page canvas on marketing pages, alternating sections |
| `brand.beige-50` | `#FDFBF6` | Card background on dark sections |
| `brand.beige-200` | `#E8E1D2` | Dividers on beige, disabled surfaces |
| `brand.gold` | `#D4AF37` | Accents, rules, icons, borders, focus ring on dark |
| `brand.gold-600` | `#B8962B` | Gold hover, small decorative fills |
| `brand.gold-700` | `#8A6D1F` | Gold **text on light** (meets 4.5:1) |

### 2.2 Semantic palette

| Token | Hex | Usage | Text on it |
|---|---|---|---|
| `canvas` | `#FFFFFF` | Default page background | `text.primary` |
| `canvas.muted` | `#F5F1E8` | Section background | `text.primary` |
| `surface` | `#FFFFFF` | Cards, inputs, modals | `text.primary` |
| `text.primary` | `#0A0A0A` | Body and headings | — |
| `text.secondary` | `#4A4A4A` | Supporting copy | — |
| `text.muted` | `#6B6B6B` | Metadata, placeholders | — |
| `text.on-dark` | `#F5F1E8` | Text on charcoal | — |
| `border.default` | `#E8E1D2` | Inputs, cards on white | — |
| `border.strong` | `#C9C2B2` | Hover, selected outline | — |
| `success` | `#1E6F45` | Paid, approved, in stock | `#E8F3EC` |
| `warning` | `#8A5A00` | Pending payment, low stock | `#FDF3E1` |
| `danger` | `#A3202B` | Errors, cancelled, delete | `#FBE9EA` |
| `info` | `#1F4E79` | Neutral notices, rescheduled | `#EAF1F8` |

### 2.3 Contrast rules

- Body text must reach **4.5:1**. `brand.gold` (`#D4AF37`) **fails on white** (≈2.1:1) and is therefore **never used for text on light backgrounds**. Use `brand.gold-700` instead.
- Gold on charcoal (≈8.9:1) is approved for headings and links on dark sections.
- Status colours are only used on their matching tinted background, never as plain text on white.

### 2.4 Order and payment status colours

| Status | Token | Treatment |
|---|---|---|
| Order `pending` | warning | Amber text on `#FDF3E1` |
| Order `confirmed` | info | Blue text on `#EAF1F8` |
| Order `in_production` | info | Blue text on `#EAF1F8` |
| Order `ready` | success | Green text on `#E8F3EC` |
| Order `delivered` | success (filled) | White on `#1E6F45` |
| Order `cancelled` | danger | Red text on `#FBE9EA` |
| Payment `unpaid` | neutral | `text.muted` on `#F5F1E8` |
| Payment `pending` | warning | Amber text on `#FDF3E1` |
| Payment `paid` | success (filled) | White on `#1E6F45` |
| Payment `failed` / `refunded` | danger | Red text on `#FBE9EA` |

---

## 3. Typography

| Role | Family | Fallback stack |
|---|---|---|
| Display / headings | **Playfair Display** | `"Playfair Display", "Times New Roman", serif` |
| Body / UI | **Montserrat** | `Montserrat, "Helvetica Neue", Arial, sans-serif` |
| Numerals in tables | Montserrat, tabular | `font-variant-numeric: tabular-nums` |

Fonts are self-hosted from `public/fonts` (woff2, subset latin + latin-ext) so the site does not depend on Google Fonts at runtime. Only weights 400 and 600 are loaded for Montserrat; 400 and 700 for Playfair Display.

### 3.1 Type scale

| Token | Size / line height | Family | Usage |
|---|---|---|---|
| `display.xl` | 60 / 64 (`clamp(40px, 6vw, 60px)`) | Playfair 700 | Home hero |
| `display.lg` | 48 / 56 | Playfair 700 | Section hero |
| `display.md` | 36 / 44 | Playfair 600 | Page title |
| `heading.lg` | 30 / 38 | Playfair 600 | Section heading |
| `heading.md` | 24 / 32 | Playfair 600 | Card group heading |
| `heading.sm` | 20 / 28 | Montserrat 600 | Panel heading |
| `body.lg` | 18 / 30 | Montserrat 400 | Intro copy |
| `body.md` | 16 / 26 | Montserrat 400 | Default body |
| `body.sm` | 14 / 22 | Montserrat 400 | Tables, help text |
| `label` | 12 / 16, `0.08em`, uppercase | Montserrat 600 | Buttons, field labels, eyebrow text |
| `price` | 20 / 28, tabular | Montserrat 600 | Product and cart prices |

### 3.2 Rules

- Headings never exceed 20 words; display sizes only on marketing pages.
- Body copy line length caps at **72 characters** (`max-w-prose`).
- All-caps is reserved for `label`; never set paragraphs in caps.
- Prices are formatted through one helper, never hand-concatenated (see §8).

---

## 4. Spacing, layout, and shape

### 4.1 Spacing scale (4 px base)

`0, 1=4, 2=8, 3=12, 4=16, 5=20, 6=24, 8=32, 10=40, 12=48, 16=64, 20=80, 24=96`

- Section vertical padding: **96** mobile / **128** desktop on marketing pages; **32** in admin.
- Card internal padding: **24** (mobile 16).
- Gap between form fields: **16**; between form sections: **32**.

### 4.2 Grid and breakpoints

| Breakpoint | Min width | Columns | Container |
|---|---|---|---|
| `sm` | 640 | 4 | fluid, 16 px gutters |
| `md` | 768 | 8 | fluid, 24 px gutters |
| `lg` | 1024 | 12 | 1024 px |
| `xl` | 1280 | 12 | 1200 px |
| `2xl` | 1536 | 12 | 1280 px |

Product grids: 2 columns mobile, 3 at `md`, 4 at `lg`.

### 4.3 Radius and elevation

| Token | Value | Usage |
|---|---|---|
| `radius.xs` | 2px | Buttons, inputs, badges |
| `radius.sm` | 4px | Cards, modals, dropdowns |
| `radius.pill` | 999px | Quantity steppers, tags |

Shadows are restrained:

- `shadow.sm` — `0 1px 2px rgba(10,10,10,.06)`
- `shadow.md` — `0 8px 24px rgba(10,10,10,.08)` (dropdowns, sticky bars)
- `shadow.lg` — `0 24px 48px rgba(10,10,10,.12)` (modals)

Image overlays use a gradient, never a border: `linear-gradient(to top, rgba(10,10,10,.55), transparent 55%)`.

---

## 5. Components

Each component below is implemented as a Blade component under `resources/views/components/ui/`. Classes listed are the required visual contract.

### 5.1 Buttons

| Variant | Rest | Hover | Active | Disabled |
|---|---|---|---|---|
| Primary | charcoal bg, beige text, 2px radius | charcoal-800 bg | charcoal-700 bg, 1px inset ring | 40% opacity, no shadow |
| Secondary | transparent, 1px charcoal border, charcoal text | charcoal bg, beige text | charcoal-800 | 40% opacity |
| Gold | gold bg, charcoal text | gold-600 bg | gold-700 bg | 40% opacity |
| Ghost | transparent, no border, charcoal text | beige bg | beige-200 bg | 40% opacity |
| Danger | danger bg, white text | darker red | darker red | 40% opacity |

- Height: **48px** default, **40px** compact (admin/table actions), **56px** for checkout primary.
- Padding: 24px horizontal (16px compact).
- Label in `label` typography; icons 16px with 8px gap.
- Every button has a visible `focus-visible` ring: `2px solid gold`, offset 2px.
- Loading state replaces label with a 16px spinner and sets `aria-busy="true"`; width is preserved to avoid layout shift.
- Disabled buttons keep their label; never swap to “Please wait”.

### 5.2 Form fields

- Label above field, `label` typography, `text.primary`; required fields marked with `*` and `aria-required`.
- Input height **48px**, 1px `border.default`, 2px radius, 12px horizontal padding, `body.md`.
- Focus: `border-color: charcoal`, `box-shadow: 0 0 0 3px rgba(212,175,55,.35)`.
- Error: `border.danger` + message below in `body.sm` / `danger`, with `aria-describedby`.
- Help text in `text.muted`, `body.sm`, placed below the field and above the error.
- Selects use a custom chevron; native `<select>` styling is reset.
- Textareas: minimum 4 rows, vertical resize only.
- Checkbox and radio: 20px, gold when checked, charcoal label text.
- Payment method selection uses a radio card: full-width bordered card with provider logo, label, and a short description (“Pay with card or mobile money”).
- Field groups: `<fieldset>` with `<legend>` in `heading.sm`.

### 5.3 Cards

- Product card: image 3:4 ratio, `object-cover`, beige placeholder while loading; name in `body.md`; price in `price`; optional “Sold out” badge; hover raises image scale to 1.03 over 300ms and shows a quick-add button.
- Content card: white surface, `radius.sm`, 1px `border.default`, 24px padding.
- Stat card (admin): label in `text.muted`, value in `display.md`, delta in `body.sm` with success/danger colour.
- Cards never nest more than two levels.

### 5.4 Navigation

- **Header (storefront):** 80px tall, transparent over hero then solid charcoal after 24px scroll; logo left, primary links centre, locale switch + account + cart right. Mobile: hamburger opens a full-height charcoal drawer with links in `heading.md`.
- **Footer:** charcoal background, beige text, four columns (brand, shop, help, contact) collapsing to accordions on mobile; payment marks row at the bottom.
- **Admin sidebar:** 260px, charcoal-700, beige text, active item marked by a 3px gold left bar; collapses to icons at `md`.
- Breadcrumbs: `body.sm`, `text.muted`, separator “/”, current page in `text.primary`.
- Skip link: first focusable element, jumps to `#main`.

### 5.5 Tables (admin)

- Header row: `label`, `text.muted`, beige background, no vertical borders.
- Rows: 56px, 1px `border.default` bottom, hover `beige-50`.
- Numeric columns right-aligned with tabular numerals.
- Row actions in a ghost icon button + dropdown; destructive actions always require a confirmation dialog.
- Below `md`, tables become stacked definition lists (label above value).
- Pagination: previous/next buttons plus “Page X of Y”; page size 20.

### 5.6 Feedback

| Component | Rule |
|---|---|
| Toast | Top-right desktop, top-centre mobile; auto-dismiss 5s; success/info/danger only |
| Inline alert | Full-width tinted block, icon + message, `radius.sm`, 16px padding |
| Empty state | Centred illustration or icon, one-line explanation, one primary action |
| Loading | Skeleton blocks for lists; spinner for buttons; never a blank page |
| Modal | Max 560px, `shadow.lg`, focus trapped, `Esc` closes, backdrop `rgba(10,10,10,.5)` |
| Confirmation | Title + consequence sentence + destructive and cancel buttons; cancel is default focus |

### 5.7 Badges and tags

- Badge: 24px tall, 2px radius, `label` typography, status tint background.
- Product tags (New, Limited): gold border, charcoal text on beige — not filled gold.

### 5.8 Commerce-specific components

- **Variant selector:** size and colour as 40px square swatch buttons with 1px border; selected state is charcoal fill with beige text; unavailable variants are struck through and `aria-disabled`.
- **Quantity stepper:** 40px tall, minus / value / plus, 44px touch targets, value in tabular numerals.
- **Price block:** current price in `price`; compare-at price in `body.sm` with line-through and `text.muted`; currency symbol per locale.
- **Cart summary:** sticky on desktop, line items, subtotal, shipping, total, one primary checkout button; totals right-aligned tabular.
- **Payment state panel:** shows status badge, reference, amount, and next step (“Complete payment”, “Payment received”). Never shows raw gateway JSON to customers.
- **Appointment slot grid:** 3 columns mobile, 4 at `md`; each slot 48px tall; selected in charcoal, unavailable in beige-200 with line-through.

---

## 6. Motion

- Durations: **150ms** micro (hover, focus), **300ms** standard (drawer, accordion), **500ms** page-level (hero reveal).
- Easing: `cubic-bezier(.2,.8,.2,1)`.
- Only `transform` and `opacity` are animated.
- Respect `prefers-reduced-motion`: all transitions collapse to 0ms and the hero does not parallax.

---

## 7. Accessibility checklist

- [ ] Every interactive element is reachable by keyboard and has a visible focus ring.
- [ ] Colour is never the only carrier of meaning (status badges include text).
- [ ] Form errors are announced (`role="alert"`, `aria-invalid`, `aria-describedby`).
- [ ] Images have meaningful `alt`; decorative images use empty `alt`.
- [ ] Touch targets ≥ 44×44 px.
- [ ] Page has one `<h1>`; heading levels never skip.
- [ ] Locale switch updates `<html lang="fr|en">`.
- [ ] Minimum contrast 4.5:1 body, 3:1 large text and UI borders.

---

## 8. Formatting rules

| Item | Rule | Example (FR / EN) |
|---|---|---|
| Price | `Intl.NumberFormat` with store locale and currency from settings | `1 250,00 GH₵` / `GH₵1,250.00` |
| Date | `d MMMM yyyy` | `5 novembre 2026` / `5 November 2026` |
| Time | 24-hour, store timezone | `14:30` |
| Phone | E.164 display grouping | `+233 24 000 0000` |
| Order number | Prefix + zero-padded sequence | `SRK-2026-000142` |

All formatting is produced server-side by helpers (`money()`, `localized_date()`), never in Blade with string concatenation.

---

## 9. Tailwind mapping

```js
// tailwind.config.js (extract)
export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: {
    extend: {
      colors: {
        charcoal: { DEFAULT: '#0A0A0A', 800: '#141414', 700: '#1F1F1F' },
        beige: { DEFAULT: '#F5F1E8', 50: '#FDFBF6', 200: '#E8E1D2' },
        gold: { DEFAULT: '#D4AF37', 600: '#B8962B', 700: '#8A6D1F' },
        ink: { DEFAULT: '#0A0A0A', soft: '#4A4A4A', muted: '#6B6B6B' },
        success: '#1E6F45', warning: '#8A5A00', danger: '#A3202B', info: '#1F4E79',
      },
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        sans: ['Montserrat', 'system-ui', 'sans-serif'],
      },
      borderRadius: { xs: '2px', sm: '4px' },
      boxShadow: {
        sm: '0 1px 2px rgba(10,10,10,.06)',
        md: '0 8px 24px rgba(10,10,10,.08)',
        lg: '0 24px 48px rgba(10,10,10,.12)',
      },
      transitionTimingFunction: { brand: 'cubic-bezier(.2,.8,.2,1)' },
    },
  },
};
```

Tokens are also exposed as CSS custom properties in `resources/css/app.css` (`--color-gold`, `--font-display`, …) so emails and static pages can reuse them.

---

## 10. Sample page

`design/sample-page.html` is a self-contained static page demonstrating the tokens: header, hero, product grid, variant selector, quantity stepper, checkout summary, form states, status badges, and footer. It uses no build step — open it directly in a browser. It loads Google Fonts for preview only; the application self-hosts the same families. It is the visual reference for Phase 1 layout work and is not part of the application.

---

## 11. Review checklist

- [ ] Colours match the SRS (#0A0A0A, #D4AF37, #F5F1E8).
- [ ] Typography uses Playfair Display for headings and Montserrat for body.
- [ ] Gold is not used for body text on light backgrounds.
- [ ] Buttons, fields, cards, tables, and feedback states are specified.
- [ ] Product variant, cart summary, payment panel, and appointment slot components are specified.
- [ ] Layout breakpoints and spacing scale are defined.
- [ ] Accessibility and reduced-motion rules are included.
- [ ] Price and date formatting rules are locale-aware and GHS-first.
- [ ] Tailwind mapping is ready to paste into Phase 1 setup.
