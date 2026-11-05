# SARAKI Digital Platform — Data Dictionary (v1)

| Field | Value |
|---|---|
| Task | 0.5 — Produce the data dictionary |
| Version | 1.0 — Approved at G0-final (2026-11-05) |
| Date | 2026-11-05 |
| Database | MySQL 8.0+, InnoDB, `utf8mb4_unicode_ci` |
| Currency (v1) | **GHS** (configurable for a later XOF switch) |
| Status | Approved; implemented in Task 1.4 |

---

## 1. Conventions

- Table and column names use `snake_case`; tables are plural.
- Primary keys are `BIGINT UNSIGNED AUTO_INCREMENT` unless stated otherwise.
- All tables include `created_at` and `updated_at` timestamps unless stated otherwise.
- Money is stored as `DECIMAL(12,2)` in the store currency. Currency codes are `CHAR(3)` and default to `GHS`.
- Booleans are `TINYINT(1)` with `0`/`1`.
- Enums are MySQL `ENUM` columns; values are listed in §4.
- No soft deletes in v1. Records are deactivated with `is_active` or status fields.
- Foreign keys are indexed. Delete behaviour is stated per table.
- Paystack amounts are sent as minor units: **GHS × 100 (pesewas)**. A later XOF switch also uses × 100 per Paystack.
- All stock and payment writes run inside database transactions with row locks.

### Table creation order

1. `users`
2. `password_reset_tokens`, `sessions`
3. `categories`
4. `products`
5. `product_variants`
6. `product_images`
7. `carts`
8. `cart_items`
9. `wishlists`
10. `orders`
11. `order_items`
12. `order_status_history`
13. `payments`
14. `payment_events`
15. `refunds`
16. `appointment_services`
17. `working_hours`
18. `blocked_dates`
19. `appointments`
20. `gallery_items`
21. `blog_posts`
22. `pages`
23. `contact_messages`
24. `settings`

---

## 2. Accounts

### 2.1 `users`

Admin and customer accounts.

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK, auto-increment |
| `name` | VARCHAR(120) | No | — | Display name |
| `email` | VARCHAR(191) | No | — | Unique; login identifier |
| `phone` | VARCHAR(30) | Yes | NULL | Used for delivery/contact |
| `password` | VARCHAR(255) | No | — | Argon2id/bcrypt hash |
| `role` | ENUM('admin','customer') | No | 'customer' | RBAC |
| `locale` | CHAR(2) | No | 'fr' | `fr` or `en`; configurable |
| `is_active` | TINYINT(1) | No | 1 | Deactivated users cannot log in |
| `email_verified_at` | TIMESTAMP | Yes | NULL | Optional verification |
| `remember_token` | VARCHAR(100) | Yes | NULL | Laravel remember-me |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(email)`, `INDEX(role)`, `INDEX(is_active)`.

### 2.2 `password_reset_tokens` (framework)

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `email` | VARCHAR(191) | No | — | PK |
| `token` | VARCHAR(255) | No | — | Hashed reset token |
| `created_at` | TIMESTAMP | Yes | NULL | Expiry checked in application |

### 2.3 `sessions` (framework)

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | VARCHAR(255) | No | — | PK |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK → `users.id`, `ON DELETE CASCADE` |
| `ip_address` | VARCHAR(45) | Yes | NULL | IPv4/IPv6 |
| `user_agent` | TEXT | Yes | NULL | |
| `payload` | LONGTEXT | No | — | Session data |
| `last_activity` | INT | No | — | Unix timestamp |

Indexes: `INDEX(user_id)`, `INDEX(last_activity)`.

---

## 3. Catalogue

### 3.1 `categories`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `parent_id` | BIGINT UNSIGNED | Yes | NULL | FK → `categories.id`, `ON DELETE SET NULL` |
| `name_fr` | VARCHAR(120) | No | — | French name |
| `name_en` | VARCHAR(120) | No | — | English name |
| `slug` | VARCHAR(160) | No | — | Unique, URL-safe |
| `sort_order` | INT | No | 0 | Ascending display order |
| `is_active` | TINYINT(1) | No | 1 | Hidden when 0 |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(slug)`, `INDEX(parent_id)`, `INDEX(is_active, sort_order)`.

### 3.2 `products`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `category_id` | BIGINT UNSIGNED | No | — | FK → `categories.id`, `ON DELETE RESTRICT` |
| `name_fr` | VARCHAR(180) | No | — | French name |
| `name_en` | VARCHAR(180) | No | — | English name |
| `slug` | VARCHAR(200) | No | — | Unique |
| `description_fr` | TEXT | Yes | NULL | |
| `description_en` | TEXT | Yes | NULL | |
| `base_price` | DECIMAL(12,2) | No | 0.00 | `CHECK (base_price >= 0)` |
| `base_sku` | VARCHAR(64) | No | — | Unique |
| `is_active` | TINYINT(1) | No | 1 | Hidden when 0 |
| `is_featured` | TINYINT(1) | No | 0 | Home page feature |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(slug)`, `UNIQUE(base_sku)`, `INDEX(category_id)`, `INDEX(is_active, is_featured)`, `FULLTEXT(name_fr, name_en, description_fr, description_en)`.

### 3.3 `product_variants`

Every product has at least one variant. Single-SKU products use `size = 'Default'` and `color = 'Default'`.

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `product_id` | BIGINT UNSIGNED | No | — | FK → `products.id`, `ON DELETE CASCADE` |
| `size` | VARCHAR(40) | No | 'Default' | e.g. S, M, L, XL |
| `color` | VARCHAR(60) | No | 'Default' | e.g. Black, Gold |
| `sku` | VARCHAR(64) | No | — | Globally unique |
| `stock_qty` | INT UNSIGNED | No | 0 | `CHECK (stock_qty >= 0)` |
| `price_delta` | DECIMAL(12,2) | No | 0.00 | Added to product base price; may be negative |
| `is_active` | TINYINT(1) | No | 1 | Hidden when 0 |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(sku)`, `UNIQUE(product_id, size, color)`, `INDEX(product_id, is_active)`.

### 3.4 `product_images`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `product_id` | BIGINT UNSIGNED | No | — | FK → `products.id`, `ON DELETE CASCADE` |
| `path` | VARCHAR(255) | No | — | Relative storage path |
| `alt_fr` | VARCHAR(255) | Yes | NULL | Accessibility text |
| `alt_en` | VARCHAR(255) | Yes | NULL | Accessibility text |
| `sort_order` | INT | No | 0 | Display order |
| `is_primary` | TINYINT(1) | No | 0 | One primary image per product (app-enforced) |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(product_id, sort_order)`, `INDEX(product_id, is_primary)`.

---

## 4. Commerce

### 4.1 `carts`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `user_id` | BIGINT UNSIGNED | Yes | NULL | FK → `users.id`, `ON DELETE CASCADE`; unique when present |
| `session_id` | VARCHAR(100) | Yes | NULL | Unique; guest cart |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(user_id)`, `UNIQUE(session_id)`. Exactly one owner: `user_id` or `session_id`.

### 4.2 `cart_items`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `cart_id` | BIGINT UNSIGNED | No | — | FK → `carts.id`, `ON DELETE CASCADE` |
| `product_id` | BIGINT UNSIGNED | No | — | FK → `products.id`, `ON DELETE CASCADE` |
| `variant_id` | BIGINT UNSIGNED | No | — | FK → `product_variants.id`, `ON DELETE CASCADE` |
| `quantity` | INT UNSIGNED | No | 1 | `CHECK (quantity >= 1)` |
| `unit_price` | DECIMAL(12,2) | No | — | Display snapshot; recalculated at checkout |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(cart_id, variant_id)`, `INDEX(variant_id)`, `INDEX(product_id)`.

### 4.3 `wishlists`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `user_id` | BIGINT UNSIGNED | No | — | FK → `users.id`, `ON DELETE CASCADE` |
| `product_id` | BIGINT UNSIGNED | No | — | FK → `products.id`, `ON DELETE CASCADE` |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(user_id, product_id)`, `INDEX(product_id)`.

### 4.4 `orders`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `user_id` | BIGINT UNSIGNED | No | — | FK → `users.id`, `ON DELETE RESTRICT` |
| `order_number` | VARCHAR(32) | No | — | Unique, human-readable (e.g. SRK-2026-000123) |
| `status` | ENUM('pending','confirmed','in_production','ready','delivered','cancelled') | No | 'pending' | Fulfilment status |
| `payment_status` | ENUM('unpaid','pending','paid','failed','refunded','partially_refunded') | No | 'unpaid' | Separate from fulfilment |
| `payment_method` | ENUM('paystack','cod','transfer') | No | 'paystack' | Selected at checkout |
| `subtotal` | DECIMAL(12,2) | No | 0.00 | `CHECK (subtotal >= 0)` |
| `shipping_fee` | DECIMAL(12,2) | No | 0.00 | `CHECK (shipping_fee >= 0)` |
| `total` | DECIMAL(12,2) | No | 0.00 | `CHECK (total >= 0)` |
| `currency` | CHAR(3) | No | 'GHS' | Configurable |
| `delivery_name` | VARCHAR(120) | No | — | |
| `delivery_phone` | VARCHAR(30) | No | — | |
| `delivery_email` | VARCHAR(191) | Yes | NULL | Defaults to user email |
| `delivery_address` | VARCHAR(255) | No | — | |
| `delivery_city` | VARCHAR(120) | No | — | |
| `notes` | TEXT | Yes | NULL | Customer notes |
| `placed_at` | TIMESTAMP | Yes | NULL | Set when order is created |
| `completed_at` | TIMESTAMP | Yes | NULL | Set when delivered |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(order_number)`, `INDEX(user_id)`, `INDEX(status)`, `INDEX(payment_status)`, `INDEX(created_at)`, `INDEX(payment_method)`.

### 4.5 `order_items`

Snapshots preserve history even if the product or variant changes.

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK → `orders.id`, `ON DELETE CASCADE` |
| `product_id` | BIGINT UNSIGNED | Yes | NULL | FK → `products.id`, `ON DELETE SET NULL` |
| `variant_id` | BIGINT UNSIGNED | Yes | NULL | FK → `product_variants.id`, `ON DELETE SET NULL` |
| `product_name` | VARCHAR(180) | No | — | Snapshot |
| `variant_label` | VARCHAR(120) | No | — | Snapshot, e.g. "M / Black" |
| `unit_price` | DECIMAL(12,2) | No | — | Snapshot; `CHECK (unit_price >= 0)` |
| `quantity` | INT UNSIGNED | No | — | `CHECK (quantity >= 1)` |
| `line_total` | DECIMAL(12,2) | No | — | `CHECK (line_total >= 0)` |
| `created_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(order_id)`, `INDEX(product_id)`, `INDEX(variant_id)`.

### 4.6 `order_status_history`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK → `orders.id`, `ON DELETE CASCADE` |
| `status` | ENUM('pending','confirmed','in_production','ready','delivered','cancelled') | No | — | New status |
| `note` | TEXT | Yes | NULL | Optional reason |
| `changed_by` | BIGINT UNSIGNED | Yes | NULL | FK → `users.id`, `ON DELETE SET NULL`; NULL = system |
| `created_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(order_id, created_at)`.

---

## 5. Payments

### 5.1 `payments`

One row per payment attempt. Multiple attempts may belong to one order; only one may be successful.

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `order_id` | BIGINT UNSIGNED | No | — | FK → `orders.id`, `ON DELETE RESTRICT` |
| `provider` | ENUM('paystack','offline') | No | 'paystack' | |
| `reference` | VARCHAR(100) | No | — | Unique; our idempotency key |
| `provider_reference` | VARCHAR(100) | Yes | NULL | Paystack transaction reference/ID |
| `amount` | DECIMAL(12,2) | No | — | `CHECK (amount >= 0)` |
| `currency` | CHAR(3) | No | 'GHS' | Must match order currency |
| `channel` | ENUM('card','mobile_money','bank_transfer','ussd','cash','transfer') | Yes | NULL | Set after payment/confirmation |
| `status` | ENUM('initialized','pending','success','failed','abandoned','refunded','partially_refunded') | No | 'initialized' | |
| `paid_at` | TIMESTAMP | Yes | NULL | |
| `gateway_response` | JSON | Yes | NULL | Sanitised Paystack response |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(reference)`, `INDEX(order_id)`, `INDEX(status)`, `INDEX(provider_reference)`.

### 5.2 `payment_events`

Webhook and callback log for idempotency and auditing.

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `payment_id` | BIGINT UNSIGNED | Yes | NULL | FK → `payments.id`, `ON DELETE SET NULL` |
| `provider` | VARCHAR(30) | No | 'paystack' | |
| `event_id` | VARCHAR(100) | No | — | Unique; prevents duplicate processing |
| `event_type` | VARCHAR(100) | No | — | e.g. `charge.success` |
| `reference` | VARCHAR(100) | No | — | Payment reference |
| `payload` | JSON | No | — | Raw event payload |
| `signature_valid` | TINYINT(1) | No | 0 | HMAC verification result |
| `processed_at` | TIMESTAMP | Yes | NULL | NULL until handled |
| `created_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(event_id)`, `INDEX(reference)`, `INDEX(payment_id)`, `INDEX(processed_at)`.

### 5.3 `refunds`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `payment_id` | BIGINT UNSIGNED | No | — | FK → `payments.id`, `ON DELETE RESTRICT` |
| `amount` | DECIMAL(12,2) | No | — | `CHECK (amount > 0)`; sum ≤ payment amount |
| `reason` | TEXT | Yes | NULL | |
| `provider_reference` | VARCHAR(100) | Yes | NULL | Paystack refund reference |
| `status` | ENUM('pending','success','failed') | No | 'pending' | |
| `processed_by` | BIGINT UNSIGNED | Yes | NULL | FK → `users.id`, `ON DELETE SET NULL` |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(payment_id)`, `INDEX(status)`, `INDEX(created_at)`.

---

## 6. Appointments

### 6.1 `appointment_services`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `name_fr` | VARCHAR(120) | No | — | e.g. Consultation |
| `name_en` | VARCHAR(120) | No | — | |
| `duration_min` | SMALLINT UNSIGNED | No | — | `CHECK (duration_min > 0)` |
| `is_active` | TINYINT(1) | No | 1 | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(is_active)`.

### 6.2 `working_hours`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `day_of_week` | TINYINT UNSIGNED | No | — | 0 = Sunday … 6 = Saturday; unique |
| `open_time` | TIME | Yes | NULL | Required when open |
| `close_time` | TIME | Yes | NULL | Must be after `open_time` |
| `is_closed` | TINYINT(1) | No | 0 | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(day_of_week)`, `CHECK (day_of_week BETWEEN 0 AND 6)`.

### 6.3 `blocked_dates`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `blocked_date` | DATE | No | — | Unique |
| `reason` | VARCHAR(255) | Yes | NULL | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(blocked_date)`.

### 6.4 `appointments`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `user_id` | BIGINT UNSIGNED | No | — | FK → `users.id`, `ON DELETE RESTRICT` |
| `service_id` | BIGINT UNSIGNED | No | — | FK → `appointment_services.id`, `ON DELETE RESTRICT` |
| `appointment_date` | DATE | No | — | |
| `start_time` | TIME | No | — | |
| `end_time` | TIME | No | — | Computed from service duration |
| `status` | ENUM('pending','approved','rescheduled','cancelled','completed') | No | 'pending' | |
| `notes` | TEXT | Yes | NULL | Customer notes |
| `admin_notes` | TEXT | Yes | NULL | Internal |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(user_id)`, `INDEX(service_id)`, `INDEX(appointment_date, start_time)`, `INDEX(status)`, `INDEX(appointment_date, status)`.

Overlap rule: booking runs in a transaction with a date-level lock; the application checks for any `pending`/`approved` appointment where `start_time < new_end AND end_time > new_start`. Same-slot duplicates are rejected.

---

## 7. Content

### 7.1 `gallery_items`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `title_fr` | VARCHAR(180) | No | — | |
| `title_en` | VARCHAR(180) | No | — | |
| `category` | ENUM('collection','event','completed_design') | No | 'collection' | |
| `image_path` | VARCHAR(255) | No | — | |
| `sort_order` | INT | No | 0 | |
| `is_published` | TINYINT(1) | No | 1 | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(category, is_published, sort_order)`.

### 7.2 `blog_posts`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `author_id` | BIGINT UNSIGNED | No | — | FK → `users.id`, `ON DELETE RESTRICT` |
| `title_fr` | VARCHAR(200) | No | — | |
| `title_en` | VARCHAR(200) | No | — | |
| `slug` | VARCHAR(220) | No | — | Unique |
| `excerpt_fr` | VARCHAR(500) | Yes | NULL | |
| `excerpt_en` | VARCHAR(500) | Yes | NULL | |
| `body_fr` | LONGTEXT | No | — | |
| `body_en` | LONGTEXT | No | — | |
| `cover_image` | VARCHAR(255) | Yes | NULL | |
| `status` | ENUM('draft','published') | No | 'draft' | |
| `published_at` | TIMESTAMP | Yes | NULL | Required when published |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(slug)`, `INDEX(author_id)`, `INDEX(status, published_at)`.

### 7.3 `pages`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `slug` | VARCHAR(160) | No | — | Unique; e.g. `about`, `privacy` |
| `title_fr` | VARCHAR(180) | No | — | |
| `title_en` | VARCHAR(180) | No | — | |
| `body_fr` | LONGTEXT | No | — | |
| `body_en` | LONGTEXT | No | — | |
| `is_published` | TINYINT(1) | No | 1 | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(slug)`.

### 7.4 `contact_messages`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `name` | VARCHAR(120) | No | — | |
| `email` | VARCHAR(191) | No | — | |
| `phone` | VARCHAR(30) | Yes | NULL | |
| `subject` | VARCHAR(180) | Yes | NULL | |
| `message` | TEXT | No | — | |
| `status` | ENUM('new','read','replied','archived') | No | 'new' | |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `INDEX(status, created_at)`, `INDEX(email)`.

### 7.5 `settings`

| Column | Type | Null | Default | Constraints / notes |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No | — | PK |
| `group` | VARCHAR(50) | No | 'general' | Admin UI grouping |
| `key` | VARCHAR(100) | No | — | Unique |
| `value` | JSON | Yes | NULL | Typed by key |
| `created_at` | TIMESTAMP | Yes | NULL | |
| `updated_at` | TIMESTAMP | Yes | NULL | |

Indexes: `UNIQUE(key)`, `INDEX(group)`.

Expected keys: `store_name`, `currency`, `default_locale`, `whatsapp_number`, `map_embed`, `shipping_zones`, `social_links`, `payment_methods`, `tax_display`, `order_expiry_minutes`.

---

## 8. Framework tables

Laravel 13 creates these additional tables. They are not part of the business domain but are listed for completeness.

| Table | Purpose |
|---|---|
| `migrations` | Migration history |
| `cache` | Cache entries (file cache is default; table optional) |
| `cache_locks` | Atomic cache locks |
| `jobs` | Queued jobs (unused in v1; no queues) |
| `job_batches` | Batch jobs (unused) |
| `failed_jobs` | Failed jobs (unused) |

`jobs`, `job_batches`, and `failed_jobs` were removed in Task 1.4: v1 uses synchronous mail and the scheduler, not queues. Failed-job logging uses the file driver.

---

## 9. Enum reference

| Enum | Values |
|---|---|
| `users.role` | `admin`, `customer` |
| `orders.status` | `pending`, `confirmed`, `in_production`, `ready`, `delivered`, `cancelled` |
| `orders.payment_status` | `unpaid`, `pending`, `paid`, `failed`, `refunded`, `partially_refunded` |
| `orders.payment_method` | `paystack`, `cod`, `transfer` |
| `payments.provider` | `paystack`, `offline` |
| `payments.channel` | `card`, `mobile_money`, `bank_transfer`, `ussd`, `cash`, `transfer` |
| `payments.status` | `initialized`, `pending`, `success`, `failed`, `abandoned`, `refunded`, `partially_refunded` |
| `refunds.status` | `pending`, `success`, `failed` |
| `appointments.status` | `pending`, `approved`, `rescheduled`, `cancelled`, `completed` |
| `gallery_items.category` | `collection`, `event`, `completed_design` |
| `blog_posts.status` | `draft`, `published` |
| `contact_messages.status` | `new`, `read`, `replied`, `archived` |

---

## 10. Currency and market notes

- v1 stores prices in **GHS** and sends Paystack amounts in **pesewas** (GHS × 100).
- `orders.currency` and `payments.currency` default to GHS but are not hard-coded in application logic.
- A later switch to **XOF (Niger)** requires:
  1. Changing the currency and locale settings.
  2. Re-entering prices and shipping fees.
  3. Updating Paystack keys to an account/entity enabled for XOF, or switching gateway.
  4. No schema migration for currency itself; `CHAR(3)` and `DECIMAL(12,2)` support both.
- Paystack XOF amounts are also sent × 100 per Paystack documentation.

---

## 11. Review checklist

- [ ] All 23 domain tables are represented.
- [ ] Variant stock and cart/order links are correct.
- [ ] Payment, webhook, and refund tables support idempotency.
- [ ] Foreign-key delete behaviour preserves orders and financial records.
- [ ] Indexes cover the main queries (catalogue search, orders, appointments).
- [ ] Currency is configurable; v1 defaults to GHS.
- [ ] Client approves the data dictionary at G0-final.
