# 01 — DATABASE (Mộc Xanh)

> Thực tế bản xuất hiện tại (`PROJECT_CONTEXT/ai-database/`, xuất 2026-10-04): **30 bảng nghiệp vụ**; 8 bảng framework có trong migrations nhưng bị loại khỏi export mặc định.
> ⚠️ `ai-database/INDEX.md` đang lỗi lặp mỗi bảng 2 dòng → ghi "60 bảng"; con số thật là 30.

---

## 1. Nguồn dữ liệu & cách cập nhật

### 1.1. Database Export (auto)

Lệnh: `php artisan ai:export-database` (chạy trong `src/`; file `app/Console/Commands/AiExportDatabase.php`).

Output mặc định: **`PROJECT_CONTEXT/ai-database/`** — thư mục nằm CÙNG CẤP với `src/` (code: `dirname(base_path()).'/PROJECT_CONTEXT/ai-database'`; fallback `base_path('PROJECT_CONTEXT/ai-database')` nếu project không đặt trong thư mục `src`).

Mỗi bảng:
- `tables/<bảng>.md` — cột, kiểu, nullable, default, index, FK (nếu schema khai), comment, thống kê min/max/top values, 50 dòng mẫu.
- `data/<bảng>.jsonl` — TOÀN BỘ bản ghi, mỗi dòng 1 JSON (chỉ bảng có dữ liệu mới sinh file; bảng rỗng hiển thị `-`).

Options: `--connection=`, `--output=`, `--include-framework` (thêm 8 bảng framework: `cache, cache_locks, failed_jobs, job_batches, jobs, migrations, password_reset_tokens, sessions`).

> Lệnh chỉ SELECT — không INSERT/UPDATE/DELETE/ALTER. Chunk 500 dòng, tối đa ~1.5MB/file md.

### 1.2. Vai trò file này

KHÔNG thay thế export. Mô tả: quy ước, quan hệ, logic dữ liệu, thiết kế đặc biệt, bẫy — những gì export không diễn giải được. Mọi tên cột/kiểu đối chiếu cuối cùng lấy từ `ai-database/tables/`.

---

## 2. Quy ước chung

- **PK**: `id` BIGINT UNSIGNED auto-increment.
- **FK**: cột `unsignedBigInteger` (`user_id`, `product_id`, `order_id`, `category_id`, `coupon_id`, `product_variant_id`, ...) — DB **KHÔNG khai báo FOREIGN KEY constraint**; toàn vẹn do Service/Application layer (CheckoutService, CartService, CouponService...) đảm bảo.
- **Timestamps**: mọi bảng có `created_at`/`updated_at`, NGOẠI TRỪ `coupon_products`, `coupon_categories`.
- **Tiền**: `int unsigned`, đơn vị VND, không float.
- **Enum**: MySQL backed string khớp PHP Enum `app/Enums` (11 enum — xem 02-CODE.md §3).
- **Engine/charset**: InnoDB, utf8mb4_unicode_ci.

---

## 3. Bảng `users` (migration `0001_01_01_000000`)

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | BIGINT UNSIGNED | PK |
| `name` | VARCHAR(255) | |
| `email` | VARCHAR(255) | UNIQUE |
| `phone` | VARCHAR(20) | UNIQUE, nullable |
| `email_verified_at` | TIMESTAMP nullable | |
| `password` | VARCHAR(255) | hashed |
| `role` | ENUM(`admin`,`staff`,`customer`) | default `customer` |
| `remember_token` | VARCHAR(100) nullable | |
| `created_at`/`updated_at` | TIMESTAMP nullable | |

Index: `(role, created_at)`. Quan hệ (model User): hasMany addresses/orders/wishlists/reviews/coupon_usages; order_status_histories + stock_movements qua `created_by`; hasOne `cart`. Helpers `isStaff()`, `isAdmin()`. Bản xuất hiện tại: 0 dòng (UI auth chưa làm).

## 4. Bảng `addresses`
`user_id` (index) · `full_name` · `phone` VARCHAR(20) · `province` · `district` · `ward` · `detail` · `is_default` bool. Index `(user_id, is_default)`. FK mềm → users.id.

## 5. Bảng `categories`
`parent_id` nullable tự tham chiếu · `name` · `slug` UNIQUE · `icon` nullable (tên file trong `public/assets/images/categories/`) · `description` nullable · `sort_order` smallint · `is_featured` bool · `status` ENUM(`active`,`hidden`). Indexes: `(parent_id,status,sort_order)`, `(is_featured,status)`. Scopes `active()`, `featured()`; helper `url()` → `/danh-muc/{slug}`. Dữ liệu thật: 5 dòng.

## 6. Bảng `products`
`category_id`(index, no FK) · `sku` UNIQUE · `slug` UNIQUE · `name` · `subtitle`? · `description` LONGTEXT? · `price_min` uint default 0 · `compare_price` uint default 0 · `stock_total` uint default 0 · `sold_count` uint default 0 · `rating_avg` DECIMAL(3,2) default 0 · `rating_count` uint · `view_count` uint · `is_featured` bool · `status` ENUM(`draft`,`active`,`hidden`) default `draft` · `published_at`? · `seo` JSON?. Indexes: `(status,is_featured,published_at)`, `(status,sold_count)`, `(category_id,status,price_min)`.
**FULLTEXT** `search_name(name)` tạo bằng `DB::statement('ALTER TABLE products ADD FULLTEXT search_name (name)')` SAU Schema::create — không thấy trong khai báo column; MySQL-only (SQLite dev phải skip). Model: scopes active/featured/bestSeller/search(?term); relations category/variants/images/reviews/wishlists/promotionProducts; HasOne lọc `defaultVariant()` (is_default) và `coverImage()` (is_cover); `url()` → `/san-pham/{slug}`, `formattedPriceMin()`, `incrementViews()`. Dữ liệu thật: 9 dòng.

## 7. Bảng `product_variants`
`product_id`(index) · `sku` UNIQUE · `label` · `price` uint · `compare_price` uint? · `stock` uint · `is_default` bool · `sort_order`. Indexes `(product_id,sort_order)`, `(product_id,is_default)`. 10 dòng.

## 8. Bảng `product_images`
`product_id`(index) · `path` · `thumb_path`? · `alt`? · `sort_order` · `is_cover` bool. 39 dòng.

## 9. Bảng `promotions`
`type` ENUM(`flash_sale`,`campaign`) · `name` · `description`? · `start_at` · `end_at` · `status` ENUM(`scheduled`,`active`,`ended`,`cancelled`). Index `(status,start_at,end_at)`. Scopes active/flashSale/currentlyRunning. 1 dòng (1 flash sale đang active).

## 10. Bảng `promotion_products`
`promotion_id`(index) · `product_id`(index) · `product_variant_id`?(index) · `flash_price` uint · `discount_percent` smallint uint default 0 · `qty_total` uint · `qty_sold` uint default 0 · `per_user_limit` smallint uint default 1 · `sort_order`. Index `(promotion_id,sort_order)`. Helpers `slotsLeft()`, `soldPercent()`.
⚠️ **Ngữ nghĩa giá (theo FlashSalePriceService + dữ liệu thật)**: `flash_price` = giá GỐC gạch ngang; `discount_percent` giảm TRÊN flash_price; GIÁ BÁN = round(flash_price×(100−discount_percent)/100). Ví dụ thật: 800.000 −22% → 624.000; 150.000 −35% → 97.500. `discount_percent=0` ⇒ suy % từ flash_price vs giá niêm yết.
Cập nhật slot (`qty_sold`) phải atomic update chống race.

## 11. Bảng `coupons`
`code` UNIQUE · `type` ENUM(`fixed`,`percent`,`shipping`) · `value` uint · `min_order_value` uint default 0 · `max_discount` uint? · `starts_at`? · `expires_at`? · `usage_limit`? · `per_user_limit` smallint default 1 · `status` ENUM(`active`,`inactive`) · `description`?. Index `(status,expires_at)`. Scopes active/valid(nullable-aware with now())/displayable. Relations belongsToMany products (qua coupon_products), categories (qua coupon_categories), hasMany usages. 4 dòng.

## 12–13. Pivot `coupon_products` / `coupon_categories`
Chỉ `coupon_id` + `product_id`/`category_id`; UNIQUE(coupon_id, product_id|category_id); **KHÔNG timestamps**.

## 14. Bảng `coupon_usages`
`coupon_id`(index) · `user_id`?(index) · `order_id`?(index) · `discount_amount` uint. Index `(coupon_id,user_id)`. 2 dòng.

## 15. Bảng `banners`
`position` ENUM(`home_hero`,`home_mid`,`category`,`product`) · `title` · `subtitle`? · `image` · `link`? · `sort_order` · `status` ENUM(`active`,`inactive`) · `start_at`? · `end_at`?. Index `(position,status,sort_order)`. Scopes active/position; helper `imageUrl()`. 0 dòng (hero hiện render tĩnh).

## 16. Bảng `carts`
`user_id`? UNIQUE · `cart_token` UUID-ish UNIQUE. Quy tắc: member dùng user_id, guest dùng cart_token (cookie `cart_token`, expiry 43200 phút); mỗi user tối đa 1 cart. 187 dòng (đa số guest).

## 17. Bảng `cart_items`
`cart_id`(index) · `product_id`(index) · `product_variant_id`(index) · `qty` smallint default 1. UNIQUE `(cart_id, product_variant_id)`. 4 dòng.

## 18. Bảng `wishlists`
`user_id`(index) · `product_id`(index); UNIQUE(user_id, product_id). 0 dòng — model có, UI chưa dùng.

## 19. Bảng `orders`
`order_number` UNIQUE · `user_id`?(index) · `customer_name` · `customer_phone` VARCHAR(20) · `customer_email`? · `address_snapshot` JSON NOT NULL · `note`? TEXT · `subtotal` uint · `discount_amount` uint default 0 · `coupon_id`?(index) · `coupon_code_snapshot` VARCHAR(50)? · `shipping_fee` uint default 0 · `total` uint · `payment_method` ENUM(`cod`,`bank_transfer`) (PHP chỉ bật cod) · `payment_status` ENUM(`pending`,`paid`,`refunded`) default pending · `status` ENUM(`new`,`confirmed`,`packing`,`shipping`,`delivered`,`cancelled`,`returning`) default new · `paid_at`? · `cancelled_at`?. Indexes: `(customer_phone,created_at)`, `(status,created_at)`.
**Order number**: `TMX-YYYYMMDD-XXXXX`, XXXXX = 5 ký tự alphanumeric IN HOA random, loop chống trùng (`CheckoutService::generateOrderNumber`). Mẫu thật: `TMX-20260925-G7RPB`, `TMX-20261004-BD1NH`.
`coupon_code_snapshot` thêm bởi migration `2026_09_26_000001` (guard `Schema::hasColumn`) — giữ mã coupon dù coupon bị xoá/sửa sau đó. 6 dòng.

## 20. Bảng `order_items`
`order_id`(index) · `product_id`?(index) · `product_variant_id`?(index) · `name_snapshot` · `sku_snapshot` · `image_snapshot`? · `price` uint · `qty` smallint · `subtotal` uint. Snapshot tại thời điểm mua — lịch sử đơn không phụ thuộc dữ liệu SP hiện tại. 16 dòng.

## 21. Bảng `order_status_histories`
`order_id`(index) · `status` VARCHAR · `note`? · `created_by`?(index → users.id). Index `(order_id, created_at)`. 0 dòng — **CheckoutService hiện KHÔNG ghi bảng này** (chỉ model/table sẵn cho admin flow tương lai).

## 22. Bảng `payments`
`order_id` UNIQUE · `method` VARCHAR · `amount` uint · `status` ENUM(`pending`,`success`,`failed`) · `transaction_code`? · `payload` JSON? · `paid_at`?. 0 dòng — **CheckoutService hiện KHÔNG tạo payment record** dù doc cũ nói có.

## 23. Bảng `shipments`
`order_id` UNIQUE · `carrier` ENUM(`internal`,`ghn`,`ghtk`,`vtp`) default internal · `tracking_code`? · `fee` uint · `status` VARCHAR default `pending` · `shipped_at`? · `delivered_at`?. Checkout TẠO 1 shipment/order (carrier internal, status pending). 6 dòng.

## 24. Bảng `stock_movements`
`product_variant_id`(index) · `type` ENUM(`import`,`export`,`adjust`,`order_reserve`,`order_release`) · `qty` INTEGER **SIGNED** (âm khi giảm stock) · `ref_type`? · `ref_id`? · `note`? · `created_by`?(index). Indexes `(product_variant_id,created_at)`, `(ref_type,ref_id)`.
⚠️ Thực tế checkout: ghi `type='export'`, `qty=-qty`, `ref_type='order'`, `ref_id=order->id`, note "Đặt hàng thành công" — 16/16 dòng mẫu là `export`. Giá trị `order_reserve`/`release` hiện CHƯA nơi nào ghi.

## 25. Bảng `reviews`
`product_id`(index) · `user_id`(index) · `order_id`?(index) · `rating` tinyint · `content` TEXT · `images` JSON? · `is_verified` bool · `admin_reply`? · `status` ENUM(`pending`,`approved`,`hidden`) default pending. UNIQUE `(order_id, product_id)`; indexes `(product_id,status,created_at)`, `(product_id,rating)`. 0 dòng (PDP render skeleton rỗng — component tự ẩn).

## 26. Bảng `testimonials`
`customer_name` · `customer_location`? · `orders_count` smallint · `rating` tinyint default 5 · `content` TEXT · `avatar`? · `sort_order` · `status` ENUM(`active`,`hidden`). 0 dòng.

## 27. Bảng `post_categories`
`name` · `slug` UNIQUE · `sort_order` · `status` ENUM(`active`,`hidden`). Scope `active()`. 3 dòng.

## 28. Bảng `posts`
`post_category_id`?(index) · `title` · `slug` UNIQUE · `excerpt`? · `content` LONGTEXT · `cover`? · `reading_minutes` smallint default 3 · `status` ENUM(`draft`,`published`) · `published_at`? · `view_count` uint. Index `(status,published_at)`. Model scope `published()`, cast `published_at`. 6 dòng.

## 29–32. Bảng nhỏ
- `newsletter_subscribers`: `email` UNIQUE, `status` ENUM(`subscribed`,`unsubscribed`). 0 dòng (form footer chưa POST).
- `stores`: `name`, `address`, `phone` VARCHAR(20), `hours`?, `status` ENUM(`active`,`hidden`). 0 dòng.
- `settings`: `key` UNIQUE, `value` TEXT?, `group_name` default 'general', index(group_name). 0 dòng.
- `search_terms`: `term` UNIQUE, `hits` uint default 1, index(hits). 5 dòng — ghi bởi `SearchTerm::track($keyword)` khi tìm kiếm.

---

## 33. Sơ đồ quan hệ (FK mềm — không constraint)

- users → 1:N addresses, orders, wishlists, reviews, coupon_usages; 1:N order_status_histories & stock_movements (qua `created_by`); 1:1 carts.
- categories → tự tham chiếu parent_id; 1:N products; N:N coupons (coupon_categories).
- products → N:1 categories; 1:N product_variants, product_images, reviews, promotion_products, cart_items, order_items, wishlists; N:N coupons (coupon_products).
- product_variants → N:1 products; 1:N cart_items, order_items, stock_movements; được promotion_products.product_variant_id tham chiếu nullable.
- promotions → 1:N promotion_products.
- coupons → 1:N coupon_products, coupon_categories, coupon_usages; 1:N orders (orders.coupon_id).
- carts → 1:N cart_items.
- orders → 1:N order_items, order_status_histories, coupon_usages, reviews(order_id?); 1:1 payments (chưa ghi), 1:1 shipments.

## 34. Bẫy thiết kế cần nhớ

1. **Không FK constraint** — Service phải kiểm tra tồn tại; DB không reject orphan.
2. **Tiền int VND unsigned**; chỉ format ở View (`format_vnd()`).
3. **FULLTEXT `search_name`** tạo ngoài Schema::create; chỉ MySQL; SQLite dev cần skip/xử lý riêng.
4. **orders.coupon_code_snapshot** (migration `2026_09_26_000001`, guard hasColumn) — snapshot mã coupon.
5. **coupon_products / coupon_categories KHÔNG timestamps** — đừng query created_at.
6. **stock_movements.qty SIGNED** — âm cho export/reserve.
7. **carts.user_id nullable UNIQUE** — 1 cart/user; guest dùng cart_token.
8. **payments & order_status_histories đang RÒNG**: schema có nhưng checkout hiện tại không ghi (khác mô tả cũ).
9. **orders.payment_method ENUM chứa cả `bank_transfer`** trong khi PHP Enum PaymentMethod chỉ bật COD — thêm phương thức phải sửa cả hai phía.
10. **INDEX.md auto đang lặp bảng 2 lần** (bug exporter) — tin `tables/*.md`, đếm tay khi cần.

## 35. Framework tables
`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens` (+ `migrations`) — tạo bởi `0001_01_01_*` và `2026_01_01_000099_create_framework_tables.php`. `sessions` dùng database driver. Mặc định BỊ LOẠI khỏi export; cần `--include-framework`.

## 36. Factory & Seeders
Factories: CHỈ `UserFactory`. ⚠️ `Post.php` gắn `#[UseFactory(PostFactory::class)]` nhưng `PostFactory` CHƯA TỒN TẠI → `Post::factory()` sẽ fatal; khi làm seeding blog phải tạo factory hoặc bỏ attribute. Seeders: chỉ `DatabaseSeeder` rỗng mặc định — dữ liệu demo hiện insert tay/seed script ngoài repo.

## 37. Workflow cập nhật context
1. Sửa/chạy migration → kiểm tra dữ liệu thật.
2. `cd src && php artisan ai:export-database`.
3. Đọc `PROJECT_CONTEXT/ai-database/INDEX.md` (nhớ bug lặp dòng) → `ai-database/tables/<bảng>.md` → `ai-database/data/<bảng>.jsonl` khi cần đủ.
4. File auto KHÔNG sửa tay. File này chỉ mô tả quy ước/quan hệ/bẫy — không thay dữ liệu thực tế.