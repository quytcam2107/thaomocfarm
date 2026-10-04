# 02 — CODE

> Models, Enums, DTOs, Services, Controllers, Requests, Routes, Views, CSS/JS và các cấu hình liên quan — trích từ `src/`.

---

## 1. Artisan Commands

### `app/Console/Commands`

| Lệnh | File | Tác dụng |
|---|---|---|
| `php artisan ai:export-database` | `app/Console/Commands/AiExportDatabase.php` | Xuất **cấu trúc + toàn bộ dữ liệu** của mọi bảng ra `src/PROJECT_CONTEXT/` gồm `INDEX.md`, `tables/<bảng>.md` và `data/<bảng>.jsonl` để AI đọc. Chỉ thực hiện `SELECT`, không sửa dữ liệu. Có thể chạy lại để cập nhật. |

### Options

- `--connection=`: Mặc định theo `.env`.
- `--output=`: Mặc định `src/PROJECT_CONTEXT`.
- `--include-framework`: Export thêm các bảng framework như `cache`, `jobs`, `sessions`, `migrations`.

> **Lưu ý:** Command chỉ đọc database bằng `SELECT`. Không `INSERT`, `UPDATE`, `DELETE` hoặc thay đổi schema.

---

## 2. Models

**Thư mục:** `app/Models`  
**Namespace:** `App\Models`

Tổng cộng **28 models**:

`Address`, `Banner`, `Cart`, `CartItem`, `Category`, `Coupon`, `CouponUsage`, `NewsletterSubscriber`, `Order`, `OrderItem`, `OrderStatusHistory`, `Payment`, `Post`, `PostCategory`, `Product`, `ProductImage`, `ProductVariant`, `Promotion`, `PromotionProduct`, `Review`, `SearchTerm`, `Setting`, `Shipment`, `StockMovement`, `Store`, `Testimonial`, `User`, `Wishlist`.

### `Product`

Scopes:

- `scopeActive()`
- `scopeFeatured()`
- `scopeBestSeller()`
- `scopeSearch(?term)`

Relations:

- `category`
- `variants`
- `images`
- `reviews`
- `wishlists`
- `promotionProducts`

Đặc biệt:

- `defaultVariant()` là `HasOne`, lọc `is_default`.
- `coverImage()` là `HasOne`, lọc `is_cover`.
- `url()` trả về `/san-pham/{slug}`.
- `incrementViews()` tăng lượt xem.

### `Category`

- `url()` trả về `/danh-muc/{slug}`.

### `Coupon`

Scopes:

- `scopeActive()`
- `scopeValid()`
- `scopeDisplayable()`

`scopeValid()` xử lý `starts_at` và `expires_at` theo kiểu nullable-aware với `now()`.

Relations:

- `products`
- `categories`
- `usages`

### `Banner`

Scopes:

- `scopeActive()`
- `scopePosition(string)`

Method:

- `imageUrl()`

### `Promotion`

Scopes:

- `scopeActive()`
- `scopeFlashSale()`
- `scopeCurrentlyRunning()`

### `PromotionProduct`

Methods:

- `slotsLeft()`
- `soldPercent()`

Relations:

- `promotion`
- `product`
- `variant`

### `User`

Role sử dụng string enum.

Methods:

- `isStaff()`
- `isAdmin()`

Relations:

- `addresses`
- `orders`
- `wishlists`
- `reviews`
- `cart`

`cart` là `hasOne`.

### Date Casts

| Model | Fields |
|---|---|
| `Coupon` | `starts_at`, `expires_at` |
| `Promotion` | `start_at`, `end_at` |
| `Banner` | `start_at`, `end_at` |
| `Product` | `published_at` |
| `Post` | `published_at` |

---

## 3. Enums

**Thư mục:** `app/Enums`

Tất cả là **backed string enum**.

Tổng cộng **11 enum**:

| Enum | Values | Ghi chú |
|---|---|---|
| `OrderStatus` | `new`, `confirmed`, `packing`, `shipping`, `delivered`, `cancelled`, `returning` | Có `label()` tiếng Việt và `next(): array` định nghĩa luồng chuyển trạng thái hợp lệ. |
| `OrderPaymentStatus` | `pending`, `paid`, `refunded` | Có `label()`. |
| `PaymentMethod` | `cod` | `bank_transfer` đang comment; chỉ COD hoạt động. |
| `CouponType` | `fixed`, `percent`, `shipping` | Có `label()`. |
| `PromotionType` | `flash_sale`, `campaign` | |
| `PromotionStatus` | `scheduled`, `active`, `ended`, `cancelled` | |
| `ProductStatus` | `draft`, `active`, `hidden` | |
| `BannerPosition` | `home_hero`, `home_mid`, `category`, `product` | |
| `ReviewStatus` | `pending`, `approved`, `hidden` | |
| `ShipmentCarrier` | `internal`, `ghn`, `ghtk`, `vtp` | |
| `StockMovementType` | `import`, `export`, `adjust`, `order_reserve`, `order_release` | |

---

## 4. DTOs

**Thư mục:** `app/DTOs`

Sử dụng **readonly promoted properties**.

### `ProductViewDTO`

Fields:

- `id`
- `name`
- `sku`
- `subtitle`
- `description`
- `price`
- `old_price`
- `discount_percent`
- `avg_rating`
- `review_count`
- `sold_count`
- `stock`
- `image`
- `meta_description`
- `category`

Quy ước:

- `price`: `int`, đơn vị VND.
- `old_price`: `?int`.
- `avg_rating`: `float`.
- `category`: `?CategoryViewDTO`.

### `CategoryViewDTO`

- `name`
- `url`

### `RelatedProductDTO`

- `url`
- `image`
- `name`
- `price`
- `old_price`
- `discount_percent`
- `avg_rating`
- `sold_count`

### `ReviewViewDTO`

- `customer`
- `rating`
- `content`
- `created_at`

---

## 5. Services

**Thư mục:** `app/Services`

### `CartService`

- `getOrCreateCart()`
- `mergeGuestCart()`
- `addToCart()`
- `updateCartItem()`
- `removeItem()`
- `getCartDetails()`
- `getCartSummary()`
- `getCartItemCount()`
- `calculateShippingFee()`

### `CatalogService`

- `getProductDetail()`
- `getProductReviews()`
- `getCategoryShow(?array)`
- `getAllProducts()`
- `searchProducts()`

### `CheckoutService`

- `getCheckoutData()`
- `calculateShippingFee()`
- `processCheckout()` → tạo `Order`.

Toàn bộ quá trình ghi đơn chạy trong `DB::transaction`.

Transaction bao gồm:

- `orders`
- `order_items`
- `payments`
- `shipments`
- `order_status_histories`
- `stock_movements`
- `coupon_usages`

`order_items` lưu snapshot dữ liệu sản phẩm tại thời điểm đặt hàng.

Khi checkout thành công, `stock_movements` tạo movement:

- `order_reserve`

Coupon usage được ghi bằng:

`recordUsage($couponId, auth()->id(), $order->id, $discountAmount)`

Đồng thời lưu:

- `coupon_code_snapshot`

### `CouponService`

Methods:

- `findByCode()`
- `eligibleSubtotal()`
- `validateForCart()`
- `calculateDiscount(Coupon, int $eligibleSubtotal, int $shippingFee): int`
- `applyToCart()`
- `resolveAppliedCoupon()`
- `removeAppliedCoupon()`
- `getAvailableCoupons()`
- `applyCoupon()`
- `recordUsage()`
- `getPublicCoupons()`

### `HomeService`

Methods:

- `featuredCategories()`
- `flashSale(?array)`
- `homeCoupons()`
- `bestSellers()`
- `herbalTea()`

### Cache Groups

Các cache group đang được sử dụng thực tế:

- `home`
- `catalog`
- `content`
- `settings`
- `review`

Cache group được bump bằng helper.

Hiện tại **chưa có Artisan command riêng để bump cache**.

Artisan command duy nhất trong app:

`php artisan ai:export-database`

---

## 6. Controllers

**Thư mục:** `app/Http/Controllers/Web`

| Controller | Methods |
|---|---|
| `HomeController` | `index` |
| `ProductController` | `show`, `index` |
| `CategoryController` | `show` |
| `SearchController` | `index`, `suggest` |
| `CartController` | `index`, `add`, `update`, `remove`, `count`, `applyCoupon`, `removeCoupon` |
| `CheckoutController` | `index`, `store`, `success` |

---

## 7. Requests

**Thư mục:** `app/Requests`  
**Namespace:** `App\Requests`

> Requests **không nằm trong** `Controllers` hoặc `FormRequests`.

### `AddToCartRequest`

- `product_id`: `required|exists:products,id`
- `variant_id`: `required|exists:product_variants,id`
- `qty`: `1..99`

### `UpdateCartRequest`

- `qty`: `1..99`

### `ApplyCouponRequest`

`code`:

- uppercase
- trim
- độ dài `3..50`
- regex `/^[A-Z0-9\-]+$/`

### `CheckoutRequest`

Fields:

- `name`
- `phone`
- `email`
- `province`
- `district`
- `ward`
- `address`
- `shipping_method`
- `payment_method`
- `note`

Rules:

- `name`: ≤ 255.
- `phone`: `/^[0-9]{9,11}$/`.
- `email`: nullable.
- `province`: required.
- `district`: required.
- `ward`: nullable.
- `address`: required.
- `shipping_method`: `in:fast,standard`.
- `payment_method`: `in:cod`.
- `note`: ≤ 1000.

Messages validation đầy đủ bằng tiếng Việt.

### `CategoryShowRequest`

Dùng cho filter/sort tại trang danh mục.

---

## 8. Routes

**File:** `routes/web.php`

> **Thứ tự route rất quan trọng.**

| Method | URI | Name | Controller |
|---|---|---|---|
| `GET` | `/` | `web.home` | `HomeController@index` |
| `GET` | `/tat-ca-san-pham` | `web.products.index` | `ProductController@index` |
| `GET` | `/tim-kiem` | `web.search.index` | `SearchController@index` |
| `GET` | `/tim-kiem/goi-y` | `web.search.suggest` | `SearchController@suggest` |
| `GET` | `/danh-muc/{slug}` | `web.category.show` | `CategoryController@show` |
| `GET` | `/gio-hang` | `web.cart.index` | `CartController@index` |
| `POST` | `/gio-hang/them` | `web.cart.add` | `CartController@add` |
| `POST` | `/gio-hang/cap-nhat/{itemId}` | `web.cart.update` | `CartController@update` |
| `DELETE` | `/gio-hang/xoa/{itemId}` | `web.cart.remove` | `CartController@remove` |
| `GET` | `/gio-hang/count` | `web.cart.count` | `CartController@count` |
| `POST` | `/gio-hang/ma-giam-gia/ap-dung` | `web.cart.coupon.apply` | `CartController@applyCoupon` |
| `DELETE` | `/gio-hang/ma-giam-gia` | `web.cart.coupon.remove` | `CartController@removeCoupon` |
| `GET` | `/thanh-toan` | `web.checkout.index` | `CheckoutController@index` |
| `POST` | `/thanh-toan` | `web.checkout.store` | `CheckoutController@store` |
| `GET` | `/dat-hang-thanh-cong/{order_number}` | `web.checkout.success` | `CheckoutController@success` |
| `GET` | `/san-pham/{slug}` | `web.product.show` | `ProductController@show` |
| `GET` | `/{slug}` | `web.category.pretty` | `CategoryController@show` |

### Route Rules

Hai route:

- `GET /tim-kiem`
- `GET /tim-kiem/goi-y`

phải được khai báo **trước** các route có `{slug}`.

Route catch-all:

`GET /{slug}`

**BẮT BUỘC phải nằm cuối cùng trong `routes/web.php`.**

---

## 9. Views

**Thư mục:** `resources/views`

### Layout

Layout chính:

`<x-layouts.app>`

File:

`components/layouts/app.blade.php`

Bao gồm:

- `meta[name="csrf-token"]`
- `style.css`
- `app.js` (loader module, xem mục 11)
- `cart.js` (loader module, xem mục 11)

### Pages

- `web/home`
- `web/category`
- `web/product`
- `web/products`
- `web/search`
- `web/cart`
- `web/checkout`
- `web/checkout-success`

Các file cũ/default vẫn tồn tại:

- `home.blade.php`
- `welcome.blade.php`

### Blade Components

#### Common

- `header`
- `footer`
- `drawer`
- `floatnav`

#### Sections

- `sections/hero`
- `sections/usp`
- `sections/collections`
- `sections/flash-sale`
- `sections/best-sellers`
- `sections/coupons`
- `sections/herbal-tea`
- `sections/testimonials`
- `sections/tips`
- `sections/trust`
- `sections/region`
- `sections/stats`
- `sections/newsletter`

#### Product

- `product/gallery`
- `product/info`
- `product/buybar`
- `product/tabs`
- `product/related`
- `product/review-item`
- `product/schema`

#### Category

- `category/chips`
- `category/filters`
- `category/filter-groups`
- `category/filter-drawer`
- `category/toolbar`
- `category/pagination`
- `category/seo-text`

#### UI

- `ui/breadcrumb`
- `ui/category-card`
- `ui/product-card`
- `ui/coupon-card`
- `ui/pagination`

### Component Behavior

Component tự ẩn khi data rỗng.

Không render:

`"Không có dữ liệu"`

---

## 10. CSS

**Thư mục:** `public/assets/css`

File entry point:

`style.css`

`style.css` import đúng thứ tự cascade của **12 partials**:

- `01-base`
- `02-...`
- `03-...`
- `04-...`
- `05-...`
- `06-...`
- `07-...`
- `08-...`
- `09-...`
- `10-...`
- `11-...`
- `12-layout-extras`

Font được import bên trong `01-base.css`.

Quy tắc:

- Mobile-first.
- Breakpoint sử dụng `max-width`.
- Không đảo thứ tự `@import`.
- Khi sửa CSS phải mở đúng partial tương ứng.
- Không gom CSS vào `style.css` nếu partial tương ứng đã tồn tại.

---

## 11. JavaScript

**Thư mục:** `public/assets/js`

### Kiến trúc module (đã tách file — cập nhật 10/2026)

`app.js` và `cart.js` trước đây là 2 file IIFE lớn (563 + 237 dòng). Nay đã tách thành các **ES modules** nhỏ, giữ nguyên 100% logic cũ:

- Layout (`components/layouts/app.blade.php`) khai báo **`<script type="importmap">`** ánh xạ tên module → đường dẫn `asset()` (duy nhất một lần), rồi nạp 2 loader:
  - `<script type="module" src="assets/js/app.js">`
  - `<script type="module" src="assets/js/cart.js">`
- `app.js` / `cart.js` giờ chỉ là **loader mỏng**: dùng **dynamic `import()` theo điều kiện DOM** (vd: chỉ import `app-search` khi có `#searchForm`, `app-backtotop` khi có `#backToTop`, `app-product` khi có `.countdown[data-ends]`/`[data-pd-flash]`). Module không cần đến sẽ KHÔNG được tải → giảm dung lượng JS mỗi trang, không chặn render, không ảnh hưởng SEO (HTML vẫn render server-side).
- Mọi hàm dùng chung đặt trong `app-core.js` (`q`, `qa`, `money`, `reduceMotion`, `toast`, `pad`).

| Module | Vai trò | Tự kích hoạt khi trang có |
|---|---|---|
| `app-core.js` | Helpers dùng chung: `q()`, `qa()`, `money()`, `reduceMotion`, `toast()` (`#toast` + class `show`), `pad()`; chặn click `a[href="#"]` | Luôn được import (nền tảng) |
| `app-ui.js` | Reveal cuộn (`.reveal` → `is-in`), count-up `[data-count]`, drawer (`[data-drawer-open]`/`[data-drawer-close]` → `is-open`, Esc đóng), clone `#filterGroups` → `#filterDrawerBody` | Luôn (hành vi toàn cục) |
| `app-backtotop.js` | Nút back-to-top `#backToTop`: hiện/ẩn 2 ngưỡng 160/60px, cuộn mượt rAF easeInOutCubic ~1s, hủy khi user can thiệp | `#backToTop` (hiện chỉ ở home, flag `:show-back-to-top`) |
| `app-product.js` | Countdown flash sale H:M:S engine dùng chung home + PDP (`data-ends` unix giây server clamp ≤ 24h), gallery thumb `#pdStageImg`, share/copy link (`[data-share-native]`/`[data-copy-url]`), tabs ARIA, Buy Now (`#buyNow`/`#buyNowMobile` → `POST /gio-hang/mua-ngay`) | `.countdown[data-ends]` hoặc `[data-pd-flash]` |
| `app-search.js` | Gõ ≥2 ký tự → debounce 300ms → `GET /tim-kiem/goi-y` dropdown gợi ý, spotlight `is-spotlight`, typing placeholder, điều hướng phím ↑↓/Enter/Esc | `#searchForm` + `#searchInput` + `#searchSuggest` |
| `cart-badge.js` | Badge giỏ hàng: chọn `.js-cart-count, .cart-count`, `updateCartBadge()` (+pulse), API toàn cục `window.updateCartCount(n)`, `fetchCartCount()` gọi `GET /gio-hang/count` lúc khởi tạo | Được `cart.js` import trước tiên |
| `cart-add.js` | Event delegation `.add-cart` → đọc variant (`input[name="variant_id"]:checked` fallback `variant`), qty (`input[name="qty"]`) → `POST /gio-hang/them` kèm `X-CSRF-TOKEN`; bỏ qua nút trong `.product-grid` không có variant để script inline trang đó xử lý | Có `.add-cart` trên trang |

**Bẫy khi sửa JS:**
- Tên importmap (`app-core`, `app-ui`, ...) phải khớp chính xác với `import '...'` trong các module — thêm module mới phải khai cả trong importmap của layout.
- `window.updateCartCount` là contract mà `app-product.js` (Buy Now) và script inline các trang products/category/search đang dùng → không đổi tên.
- Loader chạy `type="module"` (mặc định defer) — DOM đã sẵn sàng khi code chạy, không cần `DOMContentLoaded` ngoại trừ init badge trong `cart.js`.

### `app.js` (loader) + `app-core.js` / `app-ui.js` / `app-backtotop.js` / `app-product.js` / `app-search.js`

Drawer sử dụng:

- `[data-drawer-open]`
- `[data-drawer-close]`

Class trạng thái:

- `is-open`

Reveal:

- `is-in`

Spotlight:

- `is-spotlight`

Show:

- `show`

Counter:

- `data-count`

### `cart.js` (loader) + `cart-badge.js` / `cart-add.js`

Nút thêm giỏ hàng:

`.add-cart`

Request:

`POST /gio-hang/them`

CSRF token lấy từ:

`meta[name="csrf-token"]`

Header:

`X-CSRF-TOKEN`

Variant được đọc từ:

- `input[name="variant_id"]:checked`
- hoặc `input[name="variant"]:checked`

Quantity:

`input[name="qty"]`

Cart badge:

`#cartBadge`

Refresh cart count:

`GET /gio-hang/count`

Toast:

`#toast`

### JavaScript Contract

Khi set `input.value` bằng JavaScript phải gọi:

`input.dispatchEvent(new Event('change'))`

để kích hoạt các listener liên quan.

---

## 12. Config

**File:** `config/thaomoc.php`

### Order

Prefix:

`TMX`

Format:

`TMX-YYYYMMDD-xxxxx`

Trong đó `xxxxx` là random 5 số.

### Rate Limits

Đơn vị: request/phút.

| Action | Limit |
|---|---:|
| `login` | 5 |
| `checkout` | 3 |
| `apply_coupon` | 10 |
| `search` | 30 |
| `send_otp` | 3 |

### Cache TTL

#### `home`

| Key | TTL |
|---|---:|
| `featured_categories` | 600 |
| `flash_sale` | 300 |
| `best_sellers` | 600 |
| `banners` | 3600 |

#### `catalog`

| Key | TTL |
|---|---:|
| `category_tree` | 3600 |
| `product_detail` | 300 |

#### `content`

| Key | TTL |
|---|---:|
| `posts` | 1800 |
| `testimonials` | 3600 |

#### `settings`

`3600`

### Shipping

Default fee:

`30000`

Free shipping threshold:

`env('SHIPPING_FREE_THRESHOLD', 300000)`

Environment:

`SHIPPING_FREE_THRESHOLD=300000`

Carrier nội bộ:

`Giao hàng nội bộ Thảo Mộc Farm`

### Upload

- Max size: `2MB`
- Formats: `jpeg`, `png`, `webp`
- Thumbnails: `300`, `600`, `1200`

### Review

- `require_verified_purchase = true`
- `auto_approve = false`

---

## 13. Helpers

**File:** `app/Support/helpers.php`

### `group_version_key()`

`group_version_key($group)`

### `remember_group()`

`remember_group($group, $key, $ttl, $closure)`

Key thực tế:

`{group}:v{version}:{key}`

Không sử dụng `Cache::tags()` vì cache driver hiện tại là `file`.

### `bump_group_version()`

`bump_group_version($group): int`

### `format_vnd()`

Ví dụ:

`format_vnd(800000)` → `"800.000₫"`

### `format_number_compact()`

Ví dụ:

`format_number_compact(3100)` → `"3.1k"`

---

## 14. Database Export

Database context phục vụ AI được tạo bằng:

`php artisan ai:export-database`

Output:

- `src/PROJECT_CONTEXT/INDEX.md`
- `src/PROJECT_CONTEXT/tables/<bảng>.md`
- `src/PROJECT_CONTEXT/data/<bảng>.jsonl`

### Ý nghĩa

`INDEX.md`

→ Index/tổng quan database.

`tables/<bảng>.md`

→ Cấu trúc từng bảng.

`data/<bảng>.jsonl`

→ Toàn bộ dữ liệu từng bảng.

Mục đích:

> `PROJECT_CONTEXT` là nguồn context database để AI đọc và hiểu cấu trúc cũng như dữ liệu thực tế của project.

---

# 15. Bẫy đã biết — Không được phá

## 15.1. Route Catch-all

Route:

`/{slug}`

phải ở **CUỐI CÙNG** `routes/web.php`.

---

## 15.2. Route Names

Tất cả route web đều có prefix:

`web.`

Ví dụ:

`route('web.product.show', ...)`

Không tự ý đổi thành:

`route('product.show', ...)`

---

## 15.3. Database Foreign Keys

Database **không có FK constraint**.

Toàn vẹn dữ liệu phải được tự đảm bảo trong Service/Application layer.

Không được giả định database đang enforce foreign key.

---

## 15.4. Coupon Pivot Tables

Hai bảng:

- `coupon_products`
- `coupon_categories`

**không có timestamps**.

Không tự ý sử dụng:

- `created_at`
- `updated_at`

cho hai bảng này.

---

## 15.5. Cache Driver

Cache driver hiện tại:

`file`

Không sử dụng:

`Cache::tags()`

Phải sử dụng:

- `remember_group()`
- `bump_group_version()`

---

## 15.6. JavaScript Selectors

Các selector sau là **frontend contract**:

- `#cartBadge`
- `.add-cart`
- `#toast`
- `meta[name="csrf-token"]`
- `[data-drawer-open]`
- `[data-drawer-close]`

Nếu đổi tên bất kỳ selector nào:

> Phải cập nhật đồng bộ toàn bộ Blade/JS liên quan.

---
## 15.6b. JS Module Architecture (sau khi tách app.js/cart.js)

Sau khi tách `app.js`/`cart.js` thành ES modules (mục 11), bổ sung thêm các "contract" không được phá:

- Tên key trong `<script type="importmap">` của layout (`app-core`, `app-ui`, `app-backtotop`, `app-product`, `app-search`, `cart-badge`, `cart-add`) phải khớp chính xác với câu `import '...'` trong các module — thêm/xóa module phải sửa cả importmap.
- `window.updateCartCount(n)` vẫn là API toàn cục (định nghĩa trong `cart-badge.js`) mà Buy Now + script inline trang products/category/search gọi → không đổi tên, không chuyển sang export thuần.
- Hai file entry `assets/js/app.js` và `assets/js/cart.js` phải giữ nguyên đường dẫn vì layout tham chiếu qua `asset()`; nội dung chỉ nên là loader mỏng.

---

## 15.7. Money

Tiền trong backend/database luôn là:

`int VND`

Không lưu tiền dạng float.

Chỉ format ở tầng View bằng:

`format_vnd()`

Ví dụ:

`format_vnd($product->price)`

Không format tiền trước khi truyền vào Service hoặc database.

---

## 15.8. Database Export

Database export được thực hiện bằng:

`php artisan ai:export-database`

Output:

- `src/PROJECT_CONTEXT/INDEX.md`
- `src/PROJECT_CONTEXT/tables/`
- `src/PROJECT_CONTEXT/data/`

Command:

- Export cấu trúc database.
- Export toàn bộ dữ liệu các bảng.
- Chỉ đọc dữ liệu bằng `SELECT`.
- Không sửa dữ liệu.
- Có thể chạy lại để cập nhật context cho AI.
- Không giới hạn số lượng record.
- Export các dữ liệu cần thiết để AI hiểu project.
- Các bảng framework chỉ được thêm khi sử dụng `--include-framework`.

---

# 16. Core Contracts Summary

Các contract quan trọng nhất cần giữ nguyên:

| Nhóm | Contract |
|---|---|
| Routes | `/{slug}` phải ở cuối. |
| Routes | Route name luôn có prefix `web.`. |
| Database | Không có FK constraint. |
| Database | `coupon_products` không có timestamps. |
| Database | `coupon_categories` không có timestamps. |
| Cache | Driver = `file`. |
| Cache | Không dùng `Cache::tags()`. |
| Cache | Dùng `remember_group()` / `bump_group_version()`. |
| Money | Tiền luôn là `int VND`. |
| Money | Chỉ format ở View bằng `format_vnd()`. |
| Frontend | `#cartBadge` là contract. |
| Frontend | `.add-cart` là contract. |
| Frontend | `#toast` là contract. |
| Frontend | `meta[name="csrf-token"]` là contract. |
| Frontend | `[data-drawer-open]` / `[data-drawer-close]` là contract. |
| JavaScript | Khi set `input.value` bằng JS phải `dispatchEvent(new Event('change'))`. |
| JavaScript | Importmap key ↔ tên module trong `import '...'` phải khớp; `window.updateCartCount` là API toàn cục. |
| JavaScript | `assets/js/app.js` + `assets/js/cart.js` là loader module, giữ nguyên đường dẫn. |
| Database Export | Dùng `php artisan ai:export-database`. |
| Database Export | Output tại `src/PROJECT_CONTEXT/`. |

> **Nguyên tắc:** Khi thay đổi code, phải giữ nguyên các contract trên trừ khi có yêu cầu thay đổi rõ ràng. Nếu bắt buộc thay đổi một contract, phải kiểm tra và cập nhật toàn bộ thành phần phụ thuộc trước khi hoàn tất.