# SARAKI Digital Platform

Bilingual (French default / English) fashion e-commerce platform for a made-to-order
tailor in **Niamey, Niger**: catalogue with size variants, Paystack card and
mobile-money payment (Ghana test mode for the course deployment), studio
appointments, a made-to-measure request flow, and a CMS for products, gallery,
blog and pages.

**Status:** deployed and live on the school server at
<http://169.239.251.102:442/~rafiatou.ali/> — **316 tests / 1,200 assertions green**.
Phase 6 hardening is complete; gates **G1–G5** await client sign-off and the client
UAT walkthrough (`docs/uat/uat-script.md`). [plan.md](plan.md) drives the schedule;
`docs/project-documentation.md` covers implementation, challenges and the
migration plan for the client's own domain.

```
saraki/
├── plan.md            ← task table, decisions D-01…D-15, gates G0–G5, risks
├── docs/              ← SRS, design system, decisions, project documentation, UAT
├── design/            ← brand assets and reference screenshots
├── deploy/            ← server-setup, runbook and XAMPP notes for the live host
├── backups/           ← local dumps, never committed
└── src/               ← the Laravel application
```

## Requirements

| | Verified with |
|---|---|
| PHP 8.3+ with `pdo_mysql`, `gd`, `intl`, `fileinfo`, `exif` | PHP 8.3.28 |
| MySQL 8 | 8.4.3 (Laragon on Windows) |
| Composer 2 | 2.10.2 |
| Node 20+ | Vite 8, Tailwind 3.4.19 |
| Laravel 13 + Breeze (Blade + Pest) | 13.40 / Breeze 2.4.2 |

No queues, Redis, Docker, Livewire or Filament by design — see D-01 in [plan.md](plan.md).
Cache, sessions and logs use files; mail uses the `log` driver locally.

## First run

```bash
cd src
composer install
npm ci
cp .env.example .env                  # then fill DB_PASSWORD
php artisan key:generate

# Two databases and one user that can reach both:
#   saraki_local    → the app
#   saraki_testing  → php artisan test (RefreshDatabase truncates it)
php artisan migrate:fresh --seed      # 29 tables + demo store
php artisan storage:link              # required: profile photos live on the public disk
npm run build                         # or `npm run dev` while working on the frontend
php artisan serve --host=127.0.0.1 --port=8123
```

`.env` is gitignored and must stay that way; only `.env.example` is committed with empty
placeholders. The school-server `.env` was created on the host itself; SMTP credentials
are still outstanding (D-12).

### Demo accounts

Seeding creates 1 admin and 4 demo customers. Password is `SEED_ADMIN_PASSWORD` if set,
otherwise `password`.

| Account | Role | Measurement sheet |
|---|---|---|
| `admin@saraki.test` | admin | — |
| `ama.serwaa@example.test` | customer | complete |
| `kwame.boateng@example.test` | customer | complete |
| `fatima.iddrisu@example.test` | customer | partial |
| `yaw.adjei@example.test` | customer | none |

Entry points: `/fr`, `/en`, `/register`, `/login`, `/profile`, `/dashboard`, `/cart`,
`/checkout`, `/orders`, `/appointments`, `/gallery`, `/journal`, `/contact`, `/wishlist`,
`/admin/dashboard`.

## Tests

```bash
php artisan test           # 316 tests / 1200 assertions
./vendor/bin/pint --test   # style gate, from src/
```

`pint --test` is clean across all 228 PHP files; run it before every commit.

## Conventions worth knowing before you edit

- **Never hard-code UI strings.** Add keys to `src/lang/{en,fr}/*.php`, or a literal to
  `src/lang/fr.json`. Route prefixes `/{fr|en}` are resolved by `SetLocale` in the order
  route → session → account preference.
- **Design tokens only.** Colours, type scale, radii and shadows live in
  `tailwind.config.js` and `src/resources/css/app.css`; no `gray-*`/`indigo-*`/`text-sm`
  in Blade. `docs/design-system.md` is the contract.
- **Components in subdirectories need dot notation:** `<x-ui.button>`, not `x-ui-button`.
- **Blade `@match`/`@case` breaks in this stack** (compiles to a ParseError). Use a PHP
  array, as `components/ui/admin-icon.blade.php` does.
- **Tailwind `theme.container.screens` values must be px**; a `%` produces an invalid
  media query that lightningcss rejects.
- **Money and measurements are locale-aware** via `money()` and `centimetres()` in
  `src/app/Support/helpers.php`, driven by `config('saraki.*')`. Do not format numbers
  with `number_format()` in views.
- **Status labels and badge colours belong to the enums** (`badgeTone()`, `label()`), not
  the views.
- **`CustomerMeasurement::BODY_FIELDS` is the single source of truth** for the measurement
  sheet — validation rules, the profile form and the tailor view all derive from it.
  Adding a field means adding it there, to `BODY_FIELDS`, to the migration, and to both
  `lang/{en,fr}/measurements.php`.
- `role` is not mass-assignable; admin promotion uses `forceFill`. Admin routes sit behind
  the `admin` middleware alias plus a `Gate::before`.

## What is left before go-live

The platform is deployed on the school server (Apache + mod_userdir, shared MySQL
database with `saraki_` table prefix, Paystack **test mode**). Remaining work:

1. **Client sign-off** — gates G1–G5 and the UAT walkthrough
   (`docs/uat/uat-script.md`). Responsive pass and Paystack test-mode card +
   mobile-money payments are already verified end-to-end.
2. **Content & credentials** — SMTP credentials (mail currently uses the `log`
   driver), the real Niamey address / WhatsApp number / opening hours, and the real
   product catalogue with photos and XOF pricing (all admin-configurable).
3. **Client-domain migration** — own domain + SSL, dedicated database, off-server
   backups, and a Niger-compatible payment provider (Paystack does not support
   Niger). The full plan is in `docs/project-documentation.md` § Migration; deploy
   artefacts are in `deploy/`.
4. **Currency display** — French ICU renders `1 250,00 GHS`; if the client moves to
   XOF the `money()` helper and `STORE_CURRENCY` cover the switch, but the symbol
   decision should be re-checked against the design doc.
