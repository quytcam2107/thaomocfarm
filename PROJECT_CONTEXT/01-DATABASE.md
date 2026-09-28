# 01 — DATABASE (34 bảng, trích từ migrations thật trong `src/database/migrations`)

**Quy ước chung:** PK `id` bigint auto; FK lưu dạng `unsignedBigInteger` KHÔNG ràng buộc FK; mọi bảng có `timestamps()` trừ `coupon_products`, `coupon_categories`; tiền tệ là `unsignedInteger` đơn vị **VND**; enum MySQL backed string khớp các enum PHP trong `app/Enums`.

## users (migration 0001_01_01_000000)
| Cột | Kiểu | Ghi chú |
|---|---|---|
| name | VARCHAR(255) | |
| email | VARCHAR(255) | UNIQUE |
| phone | VARCHAR(20) | UNIQUE, nullable |
| email_verified_at | TIMESTAMP | nullable |
| password | VARCHAR(255) | hashed |
| role | ENUM('admin','staff','customer') | default 'customer'; index(role, created_at) |
| remember_token + timestamps | | |
Quan hệ: hasMany addresses/orders/wishlists/reviews, hasOne cart. Helper: `isStaff()`, `isAdmin()`.

## addresses
user_id(index), full_name, phone(20), province, district, ward, detail, is_default(bool). Index(user_id, is_default).

## categories
parent_id(null, tự tham chiếu), name, slug(UNIQUE), icon(null), description(null), sort_order(smallint), is_featured(bool), status ENUM('active','hidden'). Index(parent_id,status,sort_order), (is_featured,status). Scope: `scopeActive`, `scopeFeatured`, helper `url()`.

## products
category_id(index), sku(UNIQUE), slug(UNIQUE), name, subtitle(null), description(longtext,null), price_min(uint, default 0), compare_price(uint, default 0), stock_total(uint), sold_count(uint), rating_avg DECIMAL(3,2), rating_count(uint), view_count(uint), is_featured(bool), status ENUM('draft','active','hidden') default 'draft', published_at(null), seo(JSON,null).
Index: (status,is_featured,published_at), (status,sold_count), (category_id,status,price_min). **FULLTEXT `search_name (name)`** tạo qua `DB::statement('ALTER TABLE products ADD FULLTEXT search_name (name)')`.
Model: scopeActive/scopeFeatured/scopeBestSeller/scopeSearch(?string), relations category/variants/images/reviews/wishlists/promotionProducts, `defaultVariant()` HasOne(is_default), `coverImage()` HasOne(is_cover), `url()`, `formattedPriceMin()`, `incrementViews()`.

## product_variants
product_id(index), sku(UNIQUE), label, price(uint), compare_price(null), stock(uint), is_default(bool), sort_order. Index(product_id,sort_order),(product_id,is_default).

## product_images
product_id(index), path, thumb_path(null), alt(null), sort_order, is_cover(bool).

## promotions
type ENUM('flash_sale','campaign'), name, description(null), start_at, end_at, status ENUM('scheduled','active','ended','cancelled'). Index(status,start_at,end_at). Scope: `scopeActive`, `scopeFlashSale`, `scopeCurrentlyRunning`.

## promotion_products
promotion_id(index), product_id(index), product_variant_id(null,index), flash_price(uint), discount_percent(smallint), qty_total(uint), qty_sold(uint), per_user_limit(smallint default 1), sort_order. Index(promotion_id,sort_order). Model helpers: `slotsLeft(): int`, `soldPercent(): int`. Cập nhật slot theo hướng atomic update.

## coupons
code(UNIQUE), type ENUM('fixed','percent','shipping'), value(uint), min_order_value(uint default 0), max_discount(null), starts_at(null), expires_at(null), usage_limit(null), per_user_limit(smallint default 1), status ENUM('active','inactive'), description(null). Index(status,expires_at). Scope: `scopeActive`, `scopeValid`(theo now), `scopeDisplayable`. Relations: products/categories (belongsToMany), usages.

## coupon_products / coupon_categories
Chỉ: coupon_id, product_id / category_id + UNIQUE(coupon_id, product_id|category_id). **KHÔNG timestamps.**

## coupon_usages
coupon_id(index), user_id(null,index), order_id(null,index), discount_amount(uint). Index(coupon_id,user_id).

## banners
position ENUM('home_hero','home_mid','category','product'), title, subtitle(null), image, link(null), sort_order, status ENUM('active','inactive'), start_at(null), end_at(null). Index(position,status,sort_order). Scope: `scopeActive`, `scopePosition(string)`, helper `imageUrl()`.

## carts
user_id(NULLABLE UNIQUE), cart_token UUID UNIQUE. Khách dùng token, thành viên dùng user_id.

## cart_items
cart_id(index), product_id(index), product_variant_id(index), qty(smallint default 1), UNIQUE(cart_id, product_variant_id).

## wishlists
user_id(index), product_id(index), UNIQUE(user_id, product_id).

## orders
order_number(UNIQUE, format TMX-YYYYMMDD-xxxxx), user_id(null,index), customer_name, customer_phone(20), customer_email(null), address_snapshot(JSON), note(null), subtotal(uint), discount_amount(uint default 0), coupon_id(null,index), shipping_fee(uint default 0), total(uint), payment_method ENUM('cod','bank_transfer'), payment_status ENUM('pending','paid','refunded'), status ENUM('new','confirmed','packing','shipping','delivered','cancelled','returning') default 'new', paid_at(null), cancelled_at(null).
Index(customer_phone,created_at), (status,created_at). **Đã thêm cột `coupon_code_snapshot` VARCHAR(50)** (migration 2026_09_26_000001, bọc `Schema::hasColumn`).

## order_items
order_id(index), product_id(null,index), product_variant_id(null,index), name_snapshot, sku_snapshot, image_snapshot(null), price(uint), qty(smallint), subtotal(uint). Snapshot tại thời điểm mua.

## order_status_histories
order_id(index), status(VARCHAR), note(null), created_by(null,index). Index(order_id,created_at).

## payments
order_id(UNIQUE), method(VARCHAR), amount(uint), status ENUM('pending','success','failed'), transaction_code(null), payload(JSON,null), paid_at(null).

## shipments
order_id(UNIQUE), carrier ENUM('internal','ghn','ghtk','vtp') default 'internal', tracking_code(null), fee(uint), status(VARCHAR default 'pending'), shipped_at(null), delivered_at(null).

## stock_movements
product_variant_id(index), type ENUM('import','export','adjust','order_reserve','order_release'), qty(INTEGER signed), ref_type(null), ref_id(null), note(null), created_by(null,index). Index(product_variant_id,created_at),(ref_type,ref_id).

## reviews
product_id(index), user_id(index), order_id(null,index), rating(tinyint), content(text), images(JSON,null), is_verified(bool), admin_reply(null), status ENUM('pending','approved','hidden') default 'pending'. UNIQUE(order_id, product_id). Index(product_id,status,created_at),(product_id,rating).

## testimonials
customer_name, customer_location(null), orders_count(smallint), rating(tinyint default 5), content(text), avatar(null), sort_order, status ENUM('active','hidden').

## post_categories
name, slug(UNIQUE), sort_order, status ENUM('active','hidden').

## posts
post_category_id(null,index), title, slug(UNIQUE), excerpt(null), content(longtext), cover(null), reading_minutes(smallint default 3), status ENUM('draft','published'), published_at(null), view_count(uint). Index(status,published_at).

## newsletter_subscribers
email(UNIQUE), status ENUM('subscribed','unsubscribed').

## stores
name, address, phone(20), hours(null), status ENUM('active','hidden').

## settings
key(UNIQUE), value(text,null), group_name(default 'general'). Index(group_name).

## search_terms
term(UNIQUE), hits(uint default 1). Index(hits). Dùng cho gợi ý tìm kiếm.

## Framework tables
`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions` (driver database), `password_reset_tokens`. Factory chỉ có `UserFactory` — dữ liệu khác dùng seeder riêng nếu cần.