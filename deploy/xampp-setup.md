# Running SARAKI on XAMPP (this machine)

Verified 2026-11-08. Everything here was checked against the running machine, not assumed.

## What is actually installed

| Component | Path | Version | State |
|---|---|---|---|
| XAMPP Apache | `C:\xampp_new\apache` | — | owns port 80 when started |
| XAMPP MySQL = **MariaDB** | `C:\xampp_new\mysql` | 10.4.32 | owns port 3306 |
| XAMPP PHP | `C:\xampp_new\php` | 8.3.28 | **extensions disabled — see below** |
| Laragon PHP (the `php` on PATH) | `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64` | 8.3.28 | complete: `pdo_mysql`, `intl`, `gd`, `fileinfo`, `exif`, `mbstring`, `openssl`, `curl`, `zip` |
| Laragon MySQL | `C:\laragon\data\mysql-8.4` | 8.4.3 | **not running**; holds the original `saraki_local`, `saraki_testing` and the unrelated `saraki_platform` |

Two stacks are installed. Only one can own port 3306 at a time. The app now runs against
**XAMPP's MariaDB**; Laragon's MySQL data is untouched and still there as a fallback.

## Recovery state (2026-11-09)

XAMPP MariaDB's original `mysql/data` directory crashed at startup because its InnoDB
statistics table was corrupt. The active `mysql/bin/my.ini` now points to the rebuilt
`mysql/data-clean-2026-11-09` directory. The damaged source remains at
`C:\xampp_new\mysql\data`; a cold copy is preserved at
`C:\xampp_new\mysql\data-recovery-2026-11-09`, and logical exports of all recoverable
databases are under `C:\xampp_new\mysql\recovery-2026-11-09\full`. The previous
configuration is backed up as `C:\xampp_new\mysql\bin\my.ini.recovery-2026-11-09.bak`.

All 185 user tables matched the recovered source by row count before the switch. The
rebuilt MariaDB starts normally on port 3306, and the storefront, catalogue and gallery
returned HTTP 200 after restart. Do not copy the damaged `mysql/data` directory over the
active directory or use the demo SQL snapshot as a replacement for the recovered data.

## Current working setup (no admin rights needed)

```bash
# 1. start MariaDB (XAMPP Control Panel → MySQL → Start, or:)
cd /c/xampp_new && ./mysql/bin/mysqld --defaults-file=mysql/bin/my.ini --standalone

# 2. from the repo
cd src
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link          # already linked on this machine
npm run build                     # or npm run dev
php artisan serve --host=127.0.0.1 --port=8123
```

Open **http://127.0.0.1:8123/fr**. `php artisan serve` uses the Laragon PHP on PATH, which
has every extension Laravel needs and generous upload limits (`upload_max_filesize=2G`,
`post_max_size=2G`, `memory_limit=512M`, `max_file_uploads=20`), so image uploads work.

Local, **uncommitted** environment changes on this machine:

- `src/.env` → `DB_CONNECTION=mariadb` (was `mysql`). Host/port/user/password unchanged.
- `src/phpunit.xml` → `DB_CONNECTION=mariadb`. **A colleague on MySQL 8 must change this one
  line back to `mysql`**, because phpunit.xml overrides `.env` during tests.

Databases in MariaDB: `saraki_local` (app) and `saraki_testing` (tests — truncated by
`RefreshDatabase`). User `saraki` exists for both `localhost` and `127.0.0.1` and uses the
password already in `src/.env`. `root` has no password, which is what phpMyAdmin uses.

## `APP_URL` must match the host you browse

**Symptom:** an upload reports success but the image never appears.

**Cause:** uploaded-file URLs are *absolute*, built from `APP_URL` in
`config/filesystems.php:44` (`rtrim(env('APP_URL'), '/').'/storage'`). `APP_URL` was still
`http://saraki.test` from the Laragon-era setup, a host that serves nothing here, so every
`<img>` pointed at a dead address even though the file was written correctly to
`storage/app/public/…` and was reachable on the real host.

Note the asymmetry that makes this confusing: `route()` follows the **request** host (which
is why the Paystack callback worked), while `Storage::disk('public')->url()` follows
**`APP_URL`** (which is why images did not).

**Fixed 2026-11-08:** `APP_URL=http://127.0.0.1:8123` in `src/.env`, then
`php artisan config:clear`. Verified: the admin images screen and the storefront product page
both render `http://127.0.0.1:8123/storage/products/…jpg`, and that URL returns
`200 image/jpeg`. If you move to the Apache vhost, set `APP_URL=http://saraki-app.test` and
clear the config cache again.

When an image breaks, check in this order:

1. the file exists under `src/storage/app/public/…`
2. `src/public/storage` is a symlink to that directory (`php artisan storage:link`)
3. `curl -I <the img src>` — 404 means a serving problem, connection refused means a dead host
4. `APP_URL` matches the address in the browser bar

## phpMyAdmin

http://localhost/phpmyadmin → sign in as `root`, no password. `saraki_local` is in the left
sidebar with **29 tables**, 14 enforced CHECK constraints and 4 FULLTEXT indexes.

To import the exported snapshot instead of migrating:

1. Create an empty database `saraki_local` (utf8mb4 / utf8mb4_unicode_ci).
2. Select it, open the **Import** tab, choose `deploy/database/saraki_xampp.sql` (69 KB,
   structure + demo data, 29 `CREATE TABLE` statements). Verified: it imports cleanly and
   yields 5 users, 20 products, 5 pages.
3. The dump includes the `migrations` table, so `php artisan migrate` afterwards is a no-op.

The dump is a convenience snapshot. **Migrations remain the source of truth** — after any
schema change, re-export rather than hand-editing the SQL.

## Serving through XAMPP Apache instead (optional, needs one admin step)

Not required — `php artisan serve` above works. Do this only if you want the site on port 80.

### 1. Enable the PHP extensions Apache uses

`C:\xampp_new\php\php.ini` ships with these commented out, so Apache-PHP cannot run Laravel
(`php -m` under XAMPP lists only 31 modules and no `pdo_mysql`). Uncomment:

```ini
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=exif      ; must stay after mbstring
extension=openssl
extension=pdo_mysql
extension=zip
```

The DLLs are already present in `C:\xampp_new\php\ext`. Also raise the upload limits — the
app accepts 6 MB gallery/blog covers and up to 12 product photos of 4 MB in one request:

```ini
upload_max_filesize = 8M
post_max_size = 64M
max_file_uploads = 20
memory_limit = 256M
```

### 2. Add a virtual host

Append to `C:\xampp_new\apache\conf\extra\httpd-vhosts.conf`. The document root **must** be
`src/public` — never the project root, which would expose `.env`, `vendor/` and app code.

```apache
<VirtualHost *:80>
    ServerName saraki-app.test
    DocumentRoot "C:/Users/Rafiatou Malam Ali/Documents/Qoder/2026-10-27/446787a6/saraki/src/public"
    <Directory "C:/Users/Rafiatou Malam Ali/Documents/Qoder/2026-10-27/446787a6/saraki/src/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/saraki-app-error.log"
    CustomLog "logs/saraki-app-access.log" combined
</VirtualHost>
```

### 3. Add the hosts entry (this is the step that needs Administrator)

In an **elevated** editor, add to `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1    saraki-app.test
```

Do not reuse `saraki.test`: it is already in the hosts file and currently falls through to
XAMPP's default "Welcome to XAMPP" page.

### 4. Restart Apache and set the URL

Restart Apache from the XAMPP Control Panel, then set `APP_URL=http://saraki-app.test` in
`src/.env` and run `php artisan config:clear`.

## Housekeeping already done

- `C:\xampp_new\htdocs\saraki` was a **stale second copy** of the project, frozen at commit
  `ac584e2` (Task 1.6) with 11 uncommitted edits, missing everything from Task 1.7 onward.
  It was renamed to `C:\xampp_new\htdocs\saraki-old-2026-11-06` — kept, not deleted. Its
  uncommitted diff is saved at `backups/htdocs-copy-uncommitted-2026-11-06.patch`.
  The only live checkout is the GitHub one in `Documents/Qoder/2026-10-27/446787a6/saraki`.
- `http://localhost/saraki/src/public/` no longer exists; it used to return 500 because the
  database it asked for was not in MariaDB.

## Payment methods at checkout

Live setting in `saraki_local.settings` (changed 2026-11-08 to unblock the till):

```json
{"paystack":true,"cod":true,"transfer":true}
```

`PaymentMethods::enabled()` returns `paystack:false, cod:true, transfer:true` on this
machine, because `PaystackGateway::isConfigured()` is `filled(config('services.paystack.secret_key'))`
and `src/.env` has `PAYSTACK_SECRET_KEY=` empty. With no method available the checkout shows
« Aucun moyen de paiement n'est disponible pour le moment » and disables the submit button —
that is intended behaviour, not a fault. COD and bank transfer were switched on so the order
flow can be exercised before the keys arrive; turn them off again in `/admin/settings` once
Paystack is live if the shop does not want them.

To enable Paystack, add **test-mode** keys to `src/.env` (never to the repo, never to chat):

```dotenv
PAYSTACK_SECRET_KEY=sk_test_...      # Paystack dashboard → Settings → API Keys & Webhooks (Test mode)
PAYSTACK_PUBLIC_KEY=pk_test_...      # optional: read into config but unused by the hosted-checkout flow
PAYSTACK_CHANNELS=card,mobile_money
```

Then `php artisan config:clear` and reload `/fr/checkout`; "Carte ou mobile money" appears.
Only the **secret key** is required. The shopper is redirected to Paystack's hosted page and
the return callback re-verifies the transaction server-side, so a local checkout completes
without a webhook. `POST /webhooks/paystack` needs a publicly reachable URL (and the same
secret key for its HMAC SHA512 check) — that arrives with hosting, D-11. Mobile money only
works if the Ghana merchant account has the channel enabled (D-14).

Known gap, logged as **task 6.10** in `plan.md`: nothing in the admin tells the owner that
Paystack is enabled but unconfigured, and `tests/Pest.php` injects a fake `sk_test_commerce`,
so the suite stays green while a real shop cannot take payment.

## Verification log (2026-11-08, commit f2778ca)

| Check | Result |
|---|---|
| `php artisan migrate --force` | `add_restocked_at_to_orders_table` applied |
| `php artisan db:seed --force` | Settings, Page, Catalogue, Appointment, Customer seeders all DONE |
| `php artisan test` | **283 passed / 1059 assertions** on MariaDB 10.4.32 |
| `./vendor/bin/pint --test` | **PASS, 228 files** |
| `php artisan route:list` | 129 routes |
| `php artisan schedule:list` | `orders:release-expired` every 5 minutes |
| Storefront HTTP | `/fr`, `/en`, `/fr/catalogue`, `/fr/gallery`, `/fr/journal`, `/fr/contact`, `/fr/appointments`, `/fr/cart`, `/login`, `/register` → 200 |
| Admin HTTP as guest | `/admin/dashboard`, `/admin/catalogue/products`, `/admin/orders`, `/admin/appointments`, `/admin/content`, `/admin/settings` → 302 to `/login` |
| SQL export round-trip | imported into a scratch database: 29 tables, 5 users, 20 products, 5 pages; scratch dropped |

Still owed: the responsive visual pass at 360/768/1024/1440 px, Paystack **test** keys,
SMTP credentials, hosting (D-11), and the French `GH₵` vs `GHS` decision.

## Backups

`php artisan backup:run` writes two timestamped archives into `src/storage/app/backups/`
(never web-reachable) and prunes anything older than `BACKUP_KEEP_DAYS` (default 14):

- `db-YYYYMMDD-HHMMSS.sql.gz` — full `mysqldump --single-transaction --routines --triggers`
  of the configured database, password passed via `MYSQL_PWD` (never on the command line).
- `uploads-YYYYMMDD-HHMMSS.zip` — the whole `storage/app/public` disk.

The command is scheduled **daily** in `routes/console.php`, so it only fires while the
Laravel scheduler runs — on this machine that means one of:

- `php artisan schedule:work` left running, or
- a Windows Task Scheduler / cron entry hitting `php artisan schedule:run` every minute.

Restore with `php artisan backup:restore {db|uploads|all} --force` (newest archive of each
kind, or `--file=name`). `--force` is mandatory because a DB restore drops and rebuilds
tables. If `mysqldump`/`mysql` are not on `PATH` (shared hosting), set `BACKUP_MYSQLDUMP`
and `BACKUP_MYSQL` in `.env` to their full paths.