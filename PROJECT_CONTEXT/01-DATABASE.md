# 01 — DATABASE

> **34 bảng nghiệp vụ + 7 bảng framework**

---

## 1. Nguồn dữ liệu & cách cập nhật

### 1.1. Database Export

**Cấu trúc chi tiết + toàn bộ dữ liệu thật** được sinh tự động bởi:

`php artisan ai:export-database`

Output:

`src/PROJECT_CONTEXT/`

Mỗi bảng gồm:

- `tables/<bảng>.md`
  - Cột.
  - Kiểu dữ liệu.
  - Index.
  - FK nếu có trong schema.
  - Thống kê `min/max/top values`.
  - 50 dòng dữ liệu mẫu.
- `data/<bảng>.jsonl`
  - Mỗi dòng tương ứng với 1 bản ghi JSON.

Có thể chạy lại command bất cứ khi nào cần cập nhật database context.

> Command chỉ thực hiện `SELECT`, không sửa dữ liệu.

### 1.2. Vai trò của file này

File này **không thay thế database export**.

Mục đích của file là mô tả:

- Quy ước database.
- Quan hệ giữa các bảng.
- Logic dữ liệu quan trọng.
- Các thiết kế đặc biệt.
- Các bẫy cần tránh.

Những thông tin này không được database export tự động diễn giải đầy đủ.

---

# 2. Quy ước chung

## 2.1. Primary Key

PK mặc định:

- `id`
- `BIGINT`
- Auto increment
- Unsigned

## 2.2. Foreign Key

Các cột FK sử dụng:

`unsignedBigInteger`

Database **KHÔNG khai báo FOREIGN KEY constraint**.

Ví dụ:

`user_id`, `product_id`, `order_id`, `category_id`, `coupon_id`, ...

Toàn vẹn dữ liệu được đảm bảo tại **Service/Application layer**.

## 2.3. Timestamps

Mặc định tất cả bảng sử dụng:

- `created_at`
- `updated_at`

Ngoại lệ:

- `coupon_products`
- `coupon_categories`

Hai bảng này **KHÔNG có timestamps**.

## 2.4. Tiền tệ

Tất cả giá trị tiền:

- Kiểu `unsignedInteger`.
- Đơn vị **VND**.
- Không sử dụng float cho tiền.

## 2.5. Enum

Enum trong MySQL sử dụng backed string và phải khớp với các PHP Enum tương ứng trong:

`app/Enums`

---

# 3. Bảng `users`

Migration:

`0001_01_01_000000`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | BIGINT | PK, auto increment |
| `name` | VARCHAR(255) | |
| `email` | VARCHAR(255) | UNIQUE |
| `phone` | VARCHAR(20) | UNIQUE, nullable |
| `email_verified_at` | TIMESTAMP | nullable |
| `password` | VARCHAR(255) | Hashed |
| `role` | ENUM(`admin`, `staff`, `customer`) | Default `customer` |
| `remember_token` | VARCHAR | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Index:

`(role, created_at)`

Quan hệ:

- `hasMany addresses`
- `hasMany orders`
- `hasMany wishlists`
- `hasMany reviews`
- `hasMany coupon_usages`
- `hasMany order_status_histories` thông qua `created_by`
- `hasMany stock_movements` thông qua `created_by`
- `hasOne cart`

Helpers:

- `isStaff()`
- `isAdmin()`

---

# 4. Bảng `addresses`

Các cột chính:

- `user_id` — index
- `full_name`
- `phone` — VARCHAR(20)
- `province`
- `district`
- `ward`
- `detail`
- `is_default` — boolean

Index:

`(user_id, is_default)`

Quan hệ:

`addresses.user_id` → `users.id`

---

# 5. Bảng `categories`

Các cột:

- `parent_id` — nullable, tự tham chiếu
- `name`
- `slug` — UNIQUE
- `icon` — nullable
- `description` — nullable
- `sort_order` — SMALLINT
- `is_featured` — boolean
- `status` — ENUM(`active`, `hidden`)

Index:

- `(parent_id, status, sort_order)`
- `(is_featured, status)`

Scope:

- `scopeActive()`
- `scopeFeatured()`

Helper:

- `url()`

URL:

`/danh-muc/{slug}`

Quan hệ:

`categories.parent_id` → `categories.id`

---

# 6. Bảng `products`

Các cột:

- `category_id` — index
- `sku` — UNIQUE
- `slug` — UNIQUE
- `name`
- `subtitle` — nullable
- `description` — LONGTEXT, nullable
- `price_min` — unsigned integer, default `0`
- `compare_price` — unsigned integer, default `0`
- `stock_total` — unsigned integer
- `sold_count` — unsigned integer
- `rating_avg` — DECIMAL(3,2)
- `rating_count` — unsigned integer
- `view_count` — unsigned integer
- `is_featured` — boolean
- `status` — ENUM(`draft`, `active`, `hidden`), default `draft`
- `published_at` — nullable
- `seo` — JSON, nullable

Index:

- `(status, is_featured, published_at)`
- `(status, sold_count)`
- `(category_id, status, price_min)`

## FULLTEXT Search

FULLTEXT index:

`search_name(name)`

Được tạo bằng:

`DB::statement('ALTER TABLE products ADD FULLTEXT search_name (name)')`

FULLTEXT index này được tạo sau `Schema::create`.

### Model Features

Scopes:

- `scopeActive()`
- `scopeFeatured()`
- `scopeBestSeller()`
- `scopeSearch(?string)`

Relations:

- `category`
- `variants`
- `images`
- `reviews`
- `wishlists`
- `promotionProducts`

Special relations:

- `defaultVariant()` — `HasOne`, lọc `is_default`
- `coverImage()` — `HasOne`, lọc `is_cover`

Methods:

- `url()`
- `formattedPriceMin()`
- `incrementViews()`

URL:

`/san-pham/{slug}`

---

# 7. Bảng `product_variants`

Các cột:

- `product_id` — index
- `sku` — UNIQUE
- `label`
- `price` — unsigned integer
- `compare_price` — nullable
- `stock` — unsigned integer
- `is_default` — boolean
- `sort_order`

Index:

- `(product_id, sort_order)`
- `(product_id, is_default)`

Quan hệ:

`product_variants.product_id` → `products.id`

---

# 8. Bảng `product_images`

Các cột:

- `product_id` — index
- `path`
- `thumb_path` — nullable
- `alt` — nullable
- `sort_order`
- `is_cover` — boolean

Quan hệ:

`product_images.product_id` → `products.id`

---

# 9. Bảng `promotions`

Các cột:

- `type` — ENUM(`flash_sale`, `campaign`)
- `name`
- `description` — nullable
- `start_at`
- `end_at`
- `status` — ENUM(`scheduled`, `active`, `ended`, `cancelled`)

Index:

`(status, start_at, end_at)`

Scopes:

- `scopeActive()`
- `scopeFlashSale()`
- `scopeCurrentlyRunning()`

---

# 10. Bảng `promotion_products`

Các cột:

- `promotion_id` — index
- `product_id` — index
- `product_variant_id` — nullable, index
- `flash_price` — unsigned integer
- `discount_percent` — SMALLINT
- `qty_total` — unsigned integer
- `qty_sold` — unsigned integer
- `per_user_limit` — SMALLINT, default `1`
- `sort_order`

Index:

`(promotion_id, sort_order)`

Model helpers:

- `slotsLeft(): int`
- `soldPercent(): int`

### Slot Update

Việc cập nhật slot phải thực hiện theo hướng **atomic update** để tránh race condition khi nhiều request cùng mua Flash Sale.

---

# 11. Bảng `coupons`

Các cột:

- `code` — UNIQUE
- `type` — ENUM(`fixed`, `percent`, `shipping`)
- `value` — unsigned integer
- `min_order_value` — unsigned integer, default `0`
- `max_discount` — nullable
- `starts_at` — nullable
- `expires_at` — nullable
- `usage_limit` — nullable
- `per_user_limit` — SMALLINT, default `1`
- `status` — ENUM(`active`, `inactive`)
- `description` — nullable

Index:

`(status, expires_at)`

Scopes:

- `scopeActive()`
- `scopeValid()` — theo `now()`
- `scopeDisplayable()`

Relations:

- `products` — belongsToMany
- `categories` — belongsToMany
- `usages`

---

# 12. Bảng `coupon_products`

Các cột:

- `coupon_id`
- `product_id`

Unique:

`UNIQUE(coupon_id, product_id)`

**KHÔNG có timestamps.**

---

# 13. Bảng `coupon_categories`

Các cột:

- `coupon_id`
- `category_id`

Unique:

`UNIQUE(coupon_id, category_id)`

**KHÔNG có timestamps.**

---

# 14. Bảng `coupon_usages`

Các cột:

- `coupon_id` — index
- `user_id` — nullable, index
- `order_id` — nullable, index
- `discount_amount` — unsigned integer

Index:

`(coupon_id, user_id)`

Quan hệ:

- `coupon_id` → `coupons.id`
- `user_id` → `users.id`
- `order_id` → `orders.id`

---

# 15. Bảng `banners`

Các cột:

- `position` — ENUM(`home_hero`, `home_mid`, `category`, `product`)
- `title`
- `subtitle` — nullable
- `image`
- `link` — nullable
- `sort_order`
- `status` — ENUM(`active`, `inactive`)
- `start_at` — nullable
- `end_at` — nullable

Index:

`(position, status, sort_order)`

Scopes:

- `scopeActive()`
- `scopePosition(string)`

Helper:

- `imageUrl()`

---

# 16. Bảng `carts`

Các cột:

- `user_id` — nullable, UNIQUE
- `cart_token` — UUID, UNIQUE

Quy tắc:

- Thành viên đăng nhập sử dụng `user_id`.
- Khách vãng lai sử dụng `cart_token`.
- Mỗi user tối đa 1 cart.

---

# 17. Bảng `cart_items`

Các cột:

- `cart_id` — index
- `product_id` — index
- `product_variant_id` — index
- `qty` — SMALLINT, default `1`

Unique:

`UNIQUE(cart_id, product_variant_id)`

Quan hệ:

- `cart_id` → `carts.id`
- `product_id` → `products.id`
- `product_variant_id` → `product_variants.id`

---

# 18. Bảng `wishlists`

Các cột:

- `user_id` — index
- `product_id` — index

Unique:

`UNIQUE(user_id, product_id)`

Quan hệ:

- `user_id` → `users.id`
- `product_id` → `products.id`

---

# 19. Bảng `orders`

Các cột:

- `order_number` — UNIQUE
- `user_id` — nullable, index
- `customer_name`
- `customer_phone` — VARCHAR(20)
- `customer_email` — nullable
- `address_snapshot` — JSON
- `note` — nullable
- `subtotal` — unsigned integer
- `discount_amount` — unsigned integer, default `0`
- `coupon_id` — nullable, index
- `shipping_fee` — unsigned integer, default `0`
- `total` — unsigned integer
- `payment_method` — ENUM(`cod`, `bank_transfer`)
- `payment_status` — ENUM(`pending`, `paid`, `refunded`)
- `status` — ENUM(`new`, `confirmed`, `packing`, `shipping`, `delivered`, `cancelled`, `returning`), default `new`
- `paid_at` — nullable
- `cancelled_at` — nullable
- `coupon_code_snapshot` — VARCHAR(50)

Order number format:

`TMX-YYYYMMDD-xxxxx`

Trong đó `xxxxx` là random 5 số.

Index:

- `(customer_phone, created_at)`
- `(status, created_at)`

### `coupon_code_snapshot`

Được thêm bởi migration:

`2026_09_26_000001`

Migration sử dụng `Schema::hasColumn`.

Mục đích:

> Đơn hàng vẫn giữ lại mã coupon đã sử dụng ngay cả khi coupon sau đó bị xóa hoặc thay đổi.

---

# 20. Bảng `order_items`

Các cột:

- `order_id` — index
- `product_id` — nullable, index
- `product_variant_id` — nullable, index
- `name_snapshot`
- `sku_snapshot`
- `image_snapshot` — nullable
- `price` — unsigned integer
- `qty` — SMALLINT
- `subtotal` — unsigned integer

> Dữ liệu sản phẩm được lưu dưới dạng **snapshot tại thời điểm mua**.

Điều này đảm bảo lịch sử đơn hàng không phụ thuộc hoàn toàn vào dữ liệu sản phẩm hiện tại.

---

# 21. Bảng `order_status_histories`

Các cột:

- `order_id` — index
- `status` — VARCHAR
- `note` — nullable
- `created_by` — nullable, index

Index:

`(order_id, created_at)`

`created_by` → `users.id`

---

# 22. Bảng `payments`

Các cột:

- `order_id` — UNIQUE
- `method` — VARCHAR
- `amount` — unsigned integer
- `status` — ENUM(`pending`, `success`, `failed`)
- `transaction_code` — nullable
- `payload` — JSON, nullable
- `paid_at` — nullable

Quan hệ:

`payments.order_id` → `orders.id`

Mỗi order tối đa 1 payment record.

---

# 23. Bảng `shipments`

Các cột:

- `order_id` — UNIQUE
- `carrier` — ENUM(`internal`, `ghn`, `ghtk`, `vtp`), default `internal`
- `tracking_code` — nullable
- `fee` — unsigned integer
- `status` — VARCHAR, default `pending`
- `shipped_at` — nullable
- `delivered_at` — nullable

Mỗi order tối đa 1 shipment record.

---

# 24. Bảng `stock_movements`

Các cột:

- `product_variant_id` — index
- `type` — ENUM(`import`, `export`, `adjust`, `order_reserve`, `order_release`)
- `qty` — INTEGER signed
- `ref_type` — nullable
- `ref_id` — nullable
- `note` — nullable
- `created_by` — nullable, index

Index:

- `(product_variant_id, created_at)`
- `(ref_type, ref_id)`

### Quan trọng

`qty` là **INTEGER signed**.

Có thể nhận giá trị âm khi:

- export
- reserve
- các nghiệp vụ giảm stock khác

---

# 25. Bảng `reviews`

Các cột:

- `product_id` — index
- `user_id` — index
- `order_id` — nullable, index
- `rating` — TINYINT
- `content` — TEXT
- `images` — JSON, nullable
- `is_verified` — boolean
- `admin_reply` — nullable
- `status` — ENUM(`pending`, `approved`, `hidden`), default `pending`

Unique:

`UNIQUE(order_id, product_id)`

Index:

- `(product_id, status, created_at)`
- `(product_id, rating)`

Quan hệ:

- `product_id` → `products.id`
- `user_id` → `users.id`
- `order_id` → `orders.id`

---

# 26. Bảng `testimonials`

Các cột:

- `customer_name`
- `customer_location` — nullable
- `orders_count` — SMALLINT
- `rating` — TINYINT, default `5`
- `content` — TEXT
- `avatar` — nullable
- `sort_order`
- `status` — ENUM(`active`, `hidden`)

---

# 27. Bảng `post_categories`

Các cột:

- `name`
- `slug` — UNIQUE
- `sort_order`
- `status` — ENUM(`active`, `hidden`)

---

# 28. Bảng `posts`

Các cột:

- `post_category_id` — nullable, index
- `title`
- `slug` — UNIQUE
- `excerpt` — nullable
- `content` — LONGTEXT
- `cover` — nullable
- `reading_minutes` — SMALLINT, default `3`
- `status` — ENUM(`draft`, `published`)
- `published_at` — nullable
- `view_count` — unsigned integer

Index:

`(status, published_at)`

Quan hệ:

`post_category_id` → `post_categories.id`

---

# 29. Bảng `newsletter_subscribers`

Các cột:

- `email` — UNIQUE
- `status` — ENUM(`subscribed`, `unsubscribed`)

---

# 30. Bảng `stores`

Các cột:

- `name`
- `address`
- `phone` — VARCHAR(20)
- `hours` — nullable
- `status` — ENUM(`active`, `hidden`)

---

# 31. Bảng `settings`

Các cột:

- `key` — UNIQUE
- `value` — TEXT, nullable
- `group_name` — default `general`

Index:

`(group_name)`

---

# 32. Bảng `search_terms`

Các cột:

- `term` — UNIQUE
- `hits` — unsigned integer, default `1`

Index:

`(hits)`

Mục đích:

> Lưu các từ khóa tìm kiếm để phục vụ chức năng gợi ý tìm kiếm.

---

# 33. Sơ đồ quan hệ

> Database **không có FK constraint**. Các quan hệ dưới đây được nối theo quy ước tên cột và được đảm bảo bởi Application/Service layer.

## 33.1. Users

`users`

→ 1:N `addresses`

→ 1:N `orders`

→ 1:N `wishlists`

→ 1:N `reviews`

→ 1:N `coupon_usages`

→ 1:N `order_status_histories` thông qua `created_by`

→ 1:N `stock_movements` thông qua `created_by`

→ 1:1 `carts`

---

## 33.2. Categories

`categories`

→ tự tham chiếu qua `parent_id`

→ 1:N `products`

→ N:N `coupons` thông qua `coupon_categories`

---

## 33.3. Products

`products`

→ N:1 `categories`

→ 1:N `product_variants`

→ 1:N `product_images`

→ 1:N `reviews`

→ 1:N `promotion_products`

→ 1:N `cart_items`

→ 1:N `order_items`

→ 1:N `wishlists`

→ N:N `coupons` thông qua `coupon_products`

---

## 33.4. Product Variants

`product_variants`

→ N:1 `products`

→ 1:N `cart_items`

→ 1:N `order_items`

→ 1:N `stock_movements`

→ được tham chiếu nullable bởi `promotion_products.product_variant_id`

---

## 33.5. Promotions

`promotions`

→ 1:N `promotion_products`

---

## 33.6. Coupons

`coupons`

→ 1:N `coupon_products`

→ 1:N `coupon_categories`

→ 1:N `coupon_usages`

→ 1:N `orders` thông qua `orders.coupon_id`

---

## 33.7. Carts

`carts`

→ 1:N `cart_items`

---

## 33.8. Orders

`orders`

→ 1:N `order_items`

→ 1:N `order_status_histories`

→ 1:N `coupon_usages`

→ 1:1 `payments`

→ 1:1 `shipments`

→ 1:N `reviews` thông qua `reviews.order_id` nullable

---

# 34. Bẫy thiết kế cần nhớ

## 34.1. Không có Foreign Key

**KHÔNG có FOREIGN KEY ở tầng database.**

Mọi kiểm tra tồn tại và toàn vẹn dữ liệu phải được thực hiện trong Service/Application layer.

Các Service liên quan gồm:

- `CheckoutService`
- `CartService`
- `CouponService`
- các Service nghiệp vụ khác khi phát sinh quan hệ dữ liệu.

Không được giả định database tự động reject orphan records.

---

## 34.2. Tiền

Tiền luôn là:

`int VND`

Database sử dụng:

`unsignedInteger`

Không sử dụng float/double cho tiền.

---

## 34.3. FULLTEXT Search

FULLTEXT:

`search_name(name)`

được tạo bằng `DB::statement` sau `Schema::create`.

Không thấy index này trong phần khai báo column thông thường.

Chỉ hỗ trợ MySQL.

Môi trường SQLite development cần bỏ qua hoặc xử lý riêng.

---

## 34.4. `orders.coupon_code_snapshot`

Column:

`coupon_code_snapshot VARCHAR(50)`

được thêm bởi migration:

`2026_09_26_000001`

Migration được bảo vệ bằng:

`Schema::hasColumn`

Mục đích:

> Coupon có thể bị xóa hoặc thay đổi sau này nhưng order vẫn giữ lại mã coupon đã được sử dụng tại thời điểm mua.

---

## 34.5. Coupon Pivot Tables

Hai bảng:

- `coupon_products`
- `coupon_categories`

**KHÔNG có timestamps.**

Không được giả định tồn tại:

- `created_at`
- `updated_at`

---

## 34.6. Stock Movement Quantity

`stock_movements.qty`

là:

`INTEGER signed`

Không phải unsigned.

Giá trị âm được sử dụng cho các nghiệp vụ giảm/tạm giữ stock như:

- `export`
- `order_reserve`

---

## 34.7. Cart User Constraint

`carts.user_id` là:

- nullable
- UNIQUE

Quy tắc:

> Mỗi user tối đa 1 cart.

Khách vãng lai sử dụng:

`cart_token`

và `cart_token` là UUID UNIQUE.

---

# 35. Framework Tables

Các bảng framework:

- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `sessions`
- `password_reset_tokens`

`sessions` sử dụng:

`database driver`

## Database Export

Mặc định command:

`php artisan ai:export-database`

**loại các bảng framework** khỏi bản export.

Muốn export đầy đủ cả framework tables:

`php artisan ai:export-database --include-framework`

---

# 36. Factory & Seeders

Factory hiện có:

`UserFactory`

Các dữ liệu khác được tạo bằng Seeder riêng khi cần.

Không giả định rằng toàn bộ bảng đều có Factory.

---

# 37. Database Context Workflow

Quy trình cập nhật database context:

1. Thay đổi migration/schema nếu cần.
2. Chạy migration.
3. Kiểm tra dữ liệu thực tế.
4. Chạy:

`php artisan ai:export-database`

5. Kiểm tra:

`src/PROJECT_CONTEXT/INDEX.md`

6. Kiểm tra cấu trúc từng bảng tại:

`src/PROJECT_CONTEXT/tables/`

7. Kiểm tra toàn bộ dữ liệu tại:

`src/PROJECT_CONTEXT/data/`

> `01 — DATABASE.md` chỉ mô tả **quy ước, quan hệ và các bẫy thiết kế**. Không dùng file này thay thế cho dữ liệu thực tế trong `PROJECT_CONTEXT`.