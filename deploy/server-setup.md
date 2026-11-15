# Server setup — Ubuntu + phpMyAdmin (school server)

Step-by-step deployment of SARAKI on the school Ubuntu 24.04 server
(`ecom-srv`, PHP via the school's stack, phpMyAdmin for the database).

> This is the **pilot/staging** path. Going live on a public domain later
> adds SSL + live Paystack keys — see `deploy/runbook.md`.

---

## 0. Before you start — check these on the server

```bash
php -v          # MUST be 8.3 or newer — Laravel 13 refuses older
composer --version   # 2.x
mysql --version  # or mariadb — phpMyAdmin tells you which is installed
node -v         # optional: only needed to rebuild assets on the server
```

If `php -v` shows anything below **8.3**, stop and ask the server admin —
the app will not run. PHP 8.3 can often be installed side-by-side:

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update && sudo apt install php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
```

Required PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`,
`xml`, `ctype`, `json`, `curl`, `zip`, `gd` (image uploads → WebP),
`bcmath`, `fileinfo`.

---

## 1. Get the code onto the server

From your PC (the repo is Git):

```bash
# on the server
cd /var/www            # or wherever the school lets you host
git clone <repo-url> saraki
cd saraki/src
```

Or upload the project folder (zip → unzip on server). **Never upload `.env`**
— you create it fresh on the server in step 3.

---

## 2. Build frontend assets

Two options — pick one:

**A. Build on your PC (recommended, no Node needed on server):**

```bash
# on your PC, inside saraki/src
npm ci && npm run build
# then upload the whole project INCLUDING public/build to the server
```

**B. Build on the server** (if Node is installed):

```bash
cd saraki/src
npm ci && npm run build
```

---

## 3. Configure `.env`

```bash
cd /var/www/saraki/src
cp .env.example .env
php artisan key:generate
nano .env
```

Set at minimum:

```env
APP_NAME=SARAKI
APP_ENV=production
APP_DEBUG=false
APP_URL=http://10.205.0.192     # or your school hostname / domain

DB_CONNECTION=mysql             # or mariadb — match phpMyAdmin
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saraki
DB_USERNAME=saraki
DB_PASSWORD=<strong password>

SESSION_SECURE_COOKIE=false     # true ONLY when you have HTTPS
```

### SMTP (transactional email)

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=<smtp host>
MAIL_PORT=587
MAIL_USERNAME=<user>
MAIL_PASSWORD=<password or app-password>
MAIL_FROM_ADDRESS="no-reply@<your-domain-or-school-domain>"
MAIL_FROM_NAME="SARAKI"
```

If the school gives you an SMTP relay, use it. Test after deploy:
`php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('you@mail.com')->subject('SARAKI'));`

### Paystack — TEST MODE for now

```env
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxx
PAYSTACK_BASE_URL=https://api.paystack.co
PAYSTACK_CHANNELS=card,mobile_money
```

Get the keys from the Paystack dashboard → Settings → API Keys & Webhooks.
Going live later = swap `test` → `live` keys and register the webhook
`https://<your-url>/webhooks/paystack` in the Paystack dashboard.

---

## 4. Database

In **phpMyAdmin**: create database `saraki` (utf8mb4) + user `saraki`
with a strong password and ALL privileges on that database only.

Then on the server:

```bash
php artisan migrate          # tables only — NO demo products
php artisan db:seed --class=SettingsSeeder   # if it exists; otherwise settings seed with first admin save
```

Create the first admin user (adjust email/name):

```bash
php artisan tinker
>>> \App\Models\User::factory()->create(['name' => 'Admin', 'email' => 'you@mail.com', 'role' => 'admin']);
>>> // then set a password via the register page or:
>>> \$u = \App\Models\User::where('email','you@mail.com')->first(); \$u->password = bcrypt('YourStrongPass123'); \$u->save();
```

> Check `database/seeders/` first — if there's a dedicated admin seeder,
> prefer it. Products/content are added by the admin in the dashboard later.

---

## 5. Storage + permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache   # if you have sudo
```

Product/gallery/journal uploads go to `storage/app/public` — back it up
(see `docs/maintenance-checklist.md`).

---

## 6. Point the web server at `src/public`

The document root must be **`/var/www/saraki/src/public`**, not the project
root. For Apache, the vhost:

```apache
DocumentRoot /var/www/saraki/src/public
<Directory /var/www/saraki/src/public>
    AllowOverride All
    Require all granted
</Directory>
```

`public/.htaccess` already ships with Laravel's rules + our cache headers.
Nginx: try `php artisan` … or copy the standard Laravel nginx config and
set `root /var/www/saraki/src/public;`.

---

## 7. Cron — REQUIRED

Without this, pending orders never expire and backups never run:

```bash
crontab -e
# add:
* * * * * cd /var/www/saraki/src && php artisan schedule:run >> /dev/null 2>&1
```

Verify: `php artisan schedule:list` should show `orders:release-expired`
and `backup:run` (daily).

---

## 8. Cache + smoke test

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Then check:

- `http://10.205.0.192/up` → 200
- `http://10.205.0.192/fr` → homepage with your hero image
- `http://10.205.0.192/fr/catalogue` → catalogue
- Register a customer → place a COD order
- Paystack test checkout → use Paystack test card `4084 0840 8408 4081`
  (any future expiry/CVV) — verify order flips to paid
- Admin login → `/admin/dashboard` loads

---

## 9. School-server specific notes

| Concern | Answer |
|---|---|
| No public domain | Fine for pilot. `APP_URL` = IP/hostname. Live Paystack still works in test mode. |
| No HTTPS | Keep `SESSION_SECURE_COOKIE=false`. Needed before real payments go live. |
| phpMyAdmin | Use it for DB creation + browsing; the app itself uses `DB_*` in `.env`. |
| Backups | `backup:run` writes to `storage/app/backups` on the same disk — **download them off-server regularly** (the checklist has the commands). |
| Who else is on the server | The site is public to anyone who can reach the IP — keep `APP_DEBUG=false`. |

---

## When you outgrow the school server

Moving to real hosting = same steps plus: domain + DNS, Let's Encrypt SSL,
`SESSION_SECURE_COOKIE=true`, live Paystack keys + webhook, SMTP from a
real domain, and off-site backup target. `deploy/runbook.md` covers it.
