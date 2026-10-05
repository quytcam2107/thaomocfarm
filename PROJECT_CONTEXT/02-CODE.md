# 02 — CODE (Mộc Xanh)

> Trích từ code THỰC TẾ trong `src/` (đối chiếu ngày 2026-10-05). Laravel `^13.17`, PHP `^8.3`, `declare(strict_types=1)` phổ biến.

---

## 1. Artisan Commands

| Lệnh | File | Tác dụng |
|---|---|---|
| `php artisan ai:export-database` | `app/Console/Commands/AiExportDatabase.php` | Xuất cấu trúc + TOÀN BỘ dữ liệu mọi bảng ra `PROJECT_CONTEXT/ai-database/` (`INDEX.md`, `tables/<bảng>.md`, `data/<bảng>.jsonl`). Chỉ SELECT. Chunk 500, sample 50 dòng/bảng, cap 1.5MB/file md. |

Options: `--connection=` (mặc định `.env`), `--output=` (mặc định `dirname(base_path()).'/PROJECT_CONTEXT/ai-database'`, fallback `base_path('PROJECT_CONTEXT/ai-database')`), `--include-framework` (thêm cache/jobs/sessions/migrations...). Đây là command DUY NHẤT của app (không có command bump cache).

## 2. Models — `app/Models` (namespace `App\Models`) — 28 models

`Address, Banner, Cart, CartItem, Category, Coupon, CouponUsage, NewsletterSubscriber, Order, OrderItem, OrderStatusHistory, Payment, Post, PostCategory, Product, ProductImage, ProductVariant, Promotion, PromotionProduct, Review, SearchTerm, Setting, Shipment, StockMovement, Store, Testimonial, User, Wishlist`

### Product
Scopes: `active()`, `featured()`, `bestSeller()`, `search(?string)` (LIKE fallback khi driver không FULLTEXT). Relations: `category`, `variants`, `images`, `reviews`, `wishlists`, `promotionProducts`; HasOne đặc biệt `defaultVariant()` (where is_default), `coverImage()` (where is_cover). Methods: `url()` → `/san-pham/{slug}`, `formattedPriceMin()`, `incrementViews()`. Cast `published_at`.

### Category
`scopeActive()`, `scopeFeatured()`, `url()` → `/danh-muc/{slug}`, self-relation parent/children, `products`.

### Coupon
Scopes: `active()`, `valid()` (nullable-aware starts_at/expires_at với now()), `displayable()`. Relations: `products` (belongsToMany), `categories` (belongsToMany), `usages`. Casts: `starts_at`, `expires_at`.

### Banner
`active()`, `position(string)`, `imageUrl()`; casts start_at/end_at.

### Promotion
`active()`, `flashSale()`, `currentlyRunning()`; casts start_at/end_at.

### PromotionProduct
Methods `slotsLeft(): int`, `soldPercent(): int`; relations `promotion`, `product`, `variant`.

### Post / PostCategory
`Post::scopePublished()`, relation `category()` (FK cột `post_category_id`), cast `published_at`. ⚠️ attribute `#[UseFactory(PostFactory::class)]` trỏ factory CHƯA tồn tại. `PostCategory::scopeActive()`.

### User
Role string enum (`admin/staff/customer`); `isStaff()`, `isAdmin()`; relations addresses/orders/wishlists/reviews/coupon_usages + `cart()` (HasOne).

### SearchTerm
Có static `track(string $keyword)` (upsert term + tăng hits) — dùng ở SearchController.

Date casts tổng hợp: Coupon(starts_at, expires_at) · Promotion(start_at, end_at) · Banner(start_at, end_at) · Product(published_at) · Post(published_at).

## 3. Enums — `app/Enums` — 11 backed string enums

| Enum | Values | Ghi chú |
|---|---|---|
| `OrderStatus` | new, confirmed, packing, shipping, delivered, cancelled, returning | `label()` tiếng Việt + `next(): array` (luồng chuyển trạng thái hợp lệ) |
| `OrderPaymentStatus` | pending, paid, refunded | `label()` |
| `PaymentMethod` | **chỉ `cod`** | `bank_transfer` đang bị COMMENT trong PHP; cột MySQL vẫn chứa cả 2 giá trị |
| `CouponType` | fixed, percent, shipping | `label()` |
| `PromotionType` | flash_sale, campaign | |
| `PromotionStatus` | scheduled, active, ended, cancelled | |
| `ProductStatus` | draft, active, hidden | |
| `BannerPosition` | home_hero, home_mid, category, product | |
| `ReviewStatus` | pending, approved, hidden | |
| `ShipmentCarrier` | internal, ghn, ghtk, vtp | |
| `StockMovementType` | import, export, adjust, order_reserve, order_release | checkout hiện dùng EXPORT |

## 4. DTOs — `app/DTOs` — readonly promoted properties

- **ProductViewDTO**: `id`, `slug` (thêm mới — cho canonical/share), `name`, `sku`, `subtitle`, `description`, `price:int`, `old_price:?int`, `discount_percent:int`, `avg_rating:float`, `review_count`, `sold_count`, `stock`, `image`, `meta_description`, `category:?CategoryViewDTO`, `flashSale:?array` (block flash PDP, null khi SP không thuộc deal).
- **CategoryViewDTO**: `name`, `url`.
- **RelatedProductDTO**: `product_id`, `variant_id:?int`, `url`, `image`, `name`, `price`, `old_price`, `discount_percent`, `avg_rating`, `sold_count`.
- **ReviewViewDTO**: `customer`, `rating`, `content`, `created_at`, `is_verified:bool` + magic `__get()`.

## 5. Services — `app/Services`

### CartService (`CartService.php`)
Constructor inject `CouponService` + `Promotion\FlashSalePriceService`. Methods: `getOrCreateCart(): Cart` (member theo user_id + merge guest; guest theo cookie `cart_token`, tạo mới Str::uuid), `mergeGuestCart(Cart)`, `addToCart(cartId, productId, variantId, qty): CartItem` (check tồn kho), `updateCartItem`, `removeItem`, `getCartDetails(int): array{items, subtotal, total_qty,...}` (giá unit qua FlashSalePriceService), `getCartSummary(int): array` (delegate tính tiền cho `Cart\CartTotals::compute`), `getCartItemCount` (sum qty), `calculateShippingFee(int)` (= standard), `calculateShippingFeeForMethod(method, subtotal)`: **fast → luôn `fast_fee` (bỏ qua ngưỡng freeship); standard → 0 nếu subtotal ≥ free_threshold, ngược lại default_fee**.

### Sub-service `Cart/CartTotals::compute(subtotal, applied, goodsDiscount, callable $shippingFeeFor): array`
Pure function giữ 100% công thức: goodsDiscount trừ trước → ship tính trên subtotal đã trừ → coupon type=shipping miễn toàn bộ ship → total = discounted + fee − shipDiscount.

### CatalogService (`CatalogService.php`)
Inject các sub-service `Catalog/*` + `FlashSalePriceService`. Public: `getProductDetail(slug): array`, `getProductReviews(productId): array`, `getCategoryShow(slug, rawFilters): ?array`, `getAllProducts(rawFilters): array`, `searchProducts(keyword, rawFilters, ?limit): array`.
Sub-services (static, stateless):
- `Catalog/ProductQueryFilters` — áp filter khoảng giá/rating/sort theo `config/catalog.php`.
- `Catalog/ProductListingMapper::toCard(Product, ?FlashSalePriceService)` — map 1 product → card array.
- `Catalog/ProductDetailFetcher::fetch(slug)` / `::fetchReviews(productId)` — query thô cache-safe.
- `Catalog/ProductDetailHydrator::hydrate(cached, reviewData, ?flashPricing)` — ghép flash price vào detail.
- `Catalog/BreadcrumbBuilder::withCurrent(crumbs)` / `::schema(crumbs)` — breadcrumb + JSON-LD.
SEO text + per_page + sort options + price ranges đọc từ `config/catalog.php` (seo_file → `src/data/catalog-seo.json`).

### CheckoutService (`CheckoutService.php`)
Inject CartService + CouponService. `const SHIPPING_METHODS = ['fast','standard']`.
- `getCheckoutData(cartId, shippingMethod='standard'): array` — trả [] khi giỏ trống (controller redirect về cart); đồng bộ công thức với CartService; trả items/subtotal/discount/discounted_subtotal/shipping_fee/shipping_discount/total/total_qty/appliedCoupon/shipping_method/free_shipping_threshold.
- `calculateShippingFee(method, subtotal)` — delegate `CartService::calculateShippingFeeForMethod`.
- `normalizeShippingMethod(method)` — whitelist, mặc định standard.
- `processCheckout(cartId, validated): Order` — resolve coupon LẦN 2 chống race; `DB::transaction` ghi: **orders** (đủ coupon_code_snapshot), **order_items** (snapshot; price = unit_price đã gồm flash discount), **shipments** (internal/pending), **stock_movements** (`type=EXPORT`, qty ÂM, ref order) sau khi trừ stock bằng conditional UPDATE nguyên tử `where stock >= qty` (affected=0 → exception "không đủ tồn kho"), coupon `recordUsage`, xóa CartItem, `removeAppliedCoupon()`. ⚠️ KHÔNG tạo payments, KHÔNG ghi order_status_histories, KHÔNG dùng type order_reserve.
- `generateOrderNumber(): string` private — `TMX-Ymd-` + `strtoupper(Str::random(5))`, loop until unique.

### CouponService (`CouponService.php`)
`findByCode(code)`, `eligibleSubtotal(cartId, coupon)` (áp theo pivot products/categories hoặc toàn giỏ), `validateForCart(coupon, cartId, userId)`, `calculateDiscount(coupon, eligibleSubtotal, shippingFee): int` (fixed/percent có max_discount/shipping), `applyToCart(code, cartId, userId)` (session-based apply), `resolveAppliedCoupon(cartId, userId): ?array`, `removeAppliedCoupon()` (xóa session), `getAvailableCoupons(cartId, userId)`, `applyCoupon(code, cartTotal, userId)` (API-style cũ), `recordUsage(couponId, userId, orderId, discountAmount)`, `getPublicCoupons(limit=8)`. Text message/description tự sinh qua `Coupon/CouponTexts` + `config/coupons.php`.

### HomeService (`HomeService.php`)
`featuredCategories()`, `flashSale(): ?array` (KHÔNG tham số — khác doc cũ), `homeCoupons()`, `bestSellers()`, `herbalTea()`. Limits/TTL/flash_hooks đọc `config/home.php`. Cache nhóm `home` (flash 300s, còn lại 600s).

### BlogService (`BlogService.php`)
`homeTips()` (4 bài mới nhất, cache nhóm `home` key `tips`), `categories()` (post_categories active + posts_count, cache `content`), `paginate(?categorySlug, perPage=9): LengthAwarePaginator` (cache `content`), `show(slug): ?array` (KHÔNG cache — view_count tăng từng lượt), `indexSeoMeta(?categorySlug)`. TTL const 1800.

### MenuService (`MenuService.php`)
`drawerCategories(): array` — categories active + withCount products đang active (chống N+1), limit theo `home.limits.featured_categories`, cache nhóm `content` key `drawer_categories`, try/catch trả [] (drawer tự ẩn).

### Promotion/FlashSalePriceService — NGUỒN SỰ THẬT DUY NHẤT cho giá deal
Quy ước: `flash_price`=giá gốc gạch ngang; giá bán=round(flash_price×(100−discount_percent)/100); percent=0 → suy từ giá niêm yết. Cache nhóm `catalog` (TTL `thaomoc.cache.catalog.flash_sale`=300). FIX hiển thị: bỏ điều kiện end_at — promotion active vẫn trả deal, hết hạn do `is_ended` (view quyết). API: `currentDeals()`, `dealsByProduct()`, `dealFor(productId, variantId, isDefaultVariant)`, `priceFor(...)`, `currentPromotionMeta()`, `pdpBlockFor(productId)`, `applyToDetailArray(cached)`, `productIdsWithDeals()`, `categoryIdsOfDeals()`, `flush()` (bump sau admin sửa deal).

### Cache groups thực dùng
`home`, `catalog`, `content`, `settings` (+ TTL `review` trong config). Bump bằng `bump_group_version()` qua tinker — chưa có artisan command.

## 6. Controllers — `app/Http/Controllers/Web` — 8 controllers

| Controller | Methods | Ghi chú |
|---|---|---|
| `HomeController` | `index` | inject HomeService+BlogService; view `web.home` với featuredCategories/flashProducts/coupons/bestSellers/herbalTeaProducts/tips |
| `ProductController` | `show(CatalogService, slug)`, `index(CategoryShowRequest, CatalogService)` | |
| `CategoryController` | `show(CategoryShowRequest, CatalogService, slug)` | |
| `SearchController` | `index(CategoryShowRequest)`, `suggest(Request): JsonResponse` | index log `SearchTerm::track`; suggest: RateLimiter 30/phút theo IP, q<2 ký tự trả rỗng, limit 6, trả {items,total,more_url} |
| `CartController` | `index`, `add`, `buyNow`, `update`, `remove`, `count`, `applyCoupon`, `removeCoupon` | add/buyNow dùng AddToCartRequest; buyNow trả thêm `redirect` → trang checkout; set cookie `cart_token` 43200 phút cho guest; applyCoupon có RateLimiter 10/phút + clear khi thành công |
| `CheckoutController` | `index(Request)`, `store(CheckoutRequest)`, `success(Request, order_number)` | index giữ `old('shipping_method')`; giỏ trống redirect cart; store: try processCheckout → redirect success theo order_number + forget cookie guest; catch → back withInput + error |
| `BlogController` | `index`, `category(slug)`, `show(slug)` | views `web.blog.index` / `web.blog.show` |
| `PageController` | `orderGuide`, `returnPolicy`, `privacyPolicy`, `terms`, `about`, `contact` | views `web.pages.*` |

## 7. FormRequests — `app/Requests` (namespace `App\Requests`) — 5 requests

- **AddToCartRequest**: `product_id required|integer|exists:products,id`; `variant_id required|integer|exists:product_variants,id`; `qty required|integer|min:1|max:99`. Messages TV.
- **UpdateCartRequest**: `qty` 1..99.
- **ApplyCouponRequest**: `code` uppercase + trim, độ dài 3..50, regex `/^[A-Z0-9\-]+$/`.
- **CheckoutRequest**: name ≤255 required · phone regex `/^[0-9]{9,11}$/` · email nullable email · province/district required · ward nullable · address required · `shipping_method in:fast,standard` · `payment_method in:cod` · note nullable ≤1000. Messages TV đầy đủ.
- **CategoryShowRequest**: validate filter/sort query (dùng cho category/products/search index).

## 8. Routes — `routes/web.php` (THỨ TỰ THẬT, catch-all bắt buộc cuối)

| # | Method | URI | Name | Action |
|---|---|---|---|---|
| 1 | GET | `/` | `web.home` | HomeController@index |
| 2 | GET | `/tat-ca-san-pham` | `web.products.index` | ProductController@index |
| 3 | GET | `/tim-kiem` | `web.search.index` | SearchController@index |
| 4 | GET | `/tim-kiem/goi-y` | `web.search.suggest` | SearchController@suggest |
| 5 | GET | `/danh-muc/{slug}` (where [a-z0-9\-]+) | `web.category.show` | CategoryController@show |
| 6 | GET | `/gio-hang` | `web.cart.index` | CartController@index |
| 7 | POST | `/gio-hang/them` | `web.cart.add` | CartController@add |
| 8 | POST | `/gio-hang/mua-ngay` | `web.cart.buy-now` | CartController@buyNow |
| 9 | POST | `/gio-hang/cap-nhat/{itemId}` | `web.cart.update` | CartController@update |
| 10 | DELETE | `/gio-hang/xoa/{itemId}` | `web.cart.remove` | CartController@remove |
| 11 | GET | `/gio-hang/count` | `web.cart.count` | CartController@count |
| 12 | POST | `/gio-hang/ma-giam-gia/ap-dung` | `web.cart.coupon.apply` | CartController@applyCoupon |
| 13 | DELETE | `/gio-hang/ma-giam-gia` | `web.cart.coupon.remove` | CartController@removeCoupon |
| 14 | GET | `/thanh-toan` | `web.checkout.index` | CheckoutController@index |
| 15 | POST | `/thanh-toan` | `web.checkout.store` | CheckoutController@store |
| 16 | GET | `/dat-hang-thanh-cong/{order_number}` | `web.checkout.success` | CheckoutController@success |
| 17 | GET | `/san-pham/{slug}` | `web.product.show` | ProductController@show |
| 18 | GET | `/huong-dan-dat-hang` | `web.page.order-guide` | PageController@orderGuide |
| 19 | GET | `/chinh-sach-doi-tra` | `web.page.return-policy` | PageController@returnPolicy |
| 20 | GET | `/chinh-sach-bao-mat` | `web.page.privacy` | PageController@privacyPolicy |
| 21 | GET | `/dieu-khoan-su-dung` | `web.page.terms` | PageController@terms |
| 22 | GET | `/gioi-thieu` | `web.page.about` | PageController@about |
| 23 | GET | `/lien-he` | `web.page.contact` | PageController@contact |
| 24 | GET | `/cam-nang` | `web.blog.index` | BlogController@index |
| 25 | GET | `/cam-nang/category/{slug}` | `web.blog.category` | BlogController@category |
| 26 | GET | `/cam-nang/{slug}` | `web.blog.show` | BlogController@show |
| 27 | GET | `/{slug}` (where [a-z0-9\-]+) | `web.category.pretty` | CategoryController@show — **CUỐI CÙNG** |

Ngoài ra `bootstrap/app.php` khai báo health `GET /up`; exceptions `shouldRenderJsonWhen` cho `api/*` hoặc expectsJson. Middleware group mặc định, chưa đăng ký throttle nào.

## 9. Views — `resources/views`

Layout: `<x-layouts.app>` (`components/layouts/app.blade.php`) — props title/seoDescription/ogType/ogImage/bodyClass/hideCatnav/hideFloatnav/showBackToTop; chứa meta csrf-token, SEO/OG tags, font preload, `style.css`, `@stack('vendorStyles')` (lightgallery CSS push riêng — KHÔNG @import vào style.css vì font import đã chiếm đầu file), **importmap**, `$schema`, header/footer/floatnav/drawer, 2 loader module.

Pages (`web/`): `home`, `category`, `product`, `products`, `search`, `cart`, `checkout`, `checkout-success`, `blog/index`, `blog/show`, `pages/{about,contact,order-guide,privacy-policy,return-policy,terms}`. File legacy còn tồn tại: `home.blade.php` (gốc), `welcome.blade.php`.

Components:
- Common: `header`, `footer`, `drawer`, `floatnav`, `back-to-top`.
- Sections (home): `hero`, `usp`, `collections`, `flash-sale`, `best-sellers`, `coupons`, `herbal-tea`, `testimonials`, `tips`, `trust`, `region`, `stats`, `newsletter`.
- Product: `gallery`, `info`, `buybar`, `tabs`, `related`, `review-item`, `schema`, `flash-block`.
- Category: `chips`, `filters`, `filter-groups`, `filter-drawer`, `toolbar`, `pagination`, `seo-text`.
- UI: `breadcrumb`, `category-card`, `product-card`, `coupon-card`, `flash-card`, `pagination`.
- Blog: `blog/suggest-product` (SP gợi ý trong bài viết).

Quy tắc: component TỰ ẨN khi data rỗng — không render "không có dữ liệu".

## 10. CSS — `public/assets/css`

Entry `style.css` = map @import **15 partials** theo đúng cascade, KHÔNG đảo thứ tự, KHÔNG viết rule trực tiếp:
`01-base, 02-header, 03-hero, 04-home, 05-product-card, 06-category-page, 07-product-detail, 08-cart, 09-checkout, 10-success, 11-coupon, 12-layout-extras, 13-static-page, 14-flash-sale, 15-blog`.
Font import nằm TRONG `01-base.css` (`@import url('../fonts.css')`) — tuyệt đối không khai lại ở style.css (tránh nạp font 2 lần). Mobile-first, breakpoint `max-width`. Vendor lightgallery CSS nạp bằng `<link>` riêng qua stack `vendorStyles` (view nào push mới tải).

## 11. JavaScript — `public/assets/js` (ES modules, không build)

Layout khai `<script type="importmap">` MỘT lần duy nhất:

```json
{"imports": {
  "@tm/core": "assets/js/app-core.js",
  "@tm/ui": "assets/js/app-ui.js",
  "@tm/backtotop": "assets/js/app-backtotop.js",
  "@tm/product": "assets/js/app-product.js",
  "@tm/search": "assets/js/app-search.js",
  "@tm/cart-badge": "assets/js/cart-badge.js",
  "@tm/cart-add": "assets/js/cart-add.js"
}}
```

Hai loader `type="module"`: `app.js` (dispatch ui/backtotop/product/search theo điều kiện DOM) và `cart.js` (badge + add). Module không cần đến KHÔNG được tải.

| Module | Vai trò | Kích hoạt khi có |
|---|---|---|
| `app-core.js` | helpers `q, qa, money, reduceMotion, toast(#toast+class show), pad`; chặn `a[href="#"]` | luôn |
| `app-ui.js` | reveal `.reveal→is-in`, count-up `[data-count]`, drawer `[data-drawer-open]/[data-drawer-close]→is-open` + Esc, clone `#filterGroups→#filterDrawerBody` | luôn |
| `app-backtotop.js` | `#backToTop` hiện/ẩn 2 ngưỡng 160/60px, cuộn rAF ~1s easeInOutCubic | `#backToTop` (flag showBackToTop của layout) |
| `app-product.js` | countdown H:M:S `[data-ends]` (unix giây, server clamp ≤24h), gallery `#pdStageImg`, share/copy `[data-share-native]`/`[data-copy-url]`, tabs ARIA, Buy Now `#buyNow`/`#buyNowMobile` → POST `/gio-hang/mua-ngay` | `.countdown[data-ends]` hoặc `[data-pd-flash]` |
| `app-search.js` | ≥2 ký tự, debounce 300ms → GET `/tim-kiem/goi-y`, dropdown + phím ↑↓/Enter/Esc, `is-spotlight`, typing placeholder | `#searchForm` + `#searchInput` + `#searchSuggest` |
| `cart-badge.js` | badge chọn `.js-cart-count, .cart-count` (trong đó có `#cartBadge`), pulse, API global `window.updateCartCount(n)`, init fetch `GET /gio-hang/count` | cart.js import trước |
| `cart-add.js` | delegation `.add-cart`: đọc `input[name="variant_id"]:checked` (fallback `variant`), `input[name="qty"]` → POST `/gio-hang/them` + header `X-CSRF-TOKEN`; BỎ QUA nút trong `.product-grid` không có variant (để script inline trang đó xử lý) | có `.add-cart` |

Vendor: `public/assets/vendor/lightgallery/*` (bundle UMD + plugins zoom/thumb/pager/share/autoplay/hash/fullscreen) — PDP gallery.

**Bẫy JS:** importmap key ↔ `import '@tm/...'` phải khớp, module mới khai cả importmap; `window.updateCartCount` là contract (Buy Now + script inline products/category/search) — không đổi tên; giữ nguyên đường dẫn `app.js`/`cart.js`; set `input.value` bằng JS phải `dispatchEvent(new Event('change'))`; loader `type=module` mặc định defer nên DOM đã sẵn sàng.

## 12. Config — `src/config` (project-specific: thaomoc, home, catalog, coupons)

### `thaomoc.php`
- `order`: prefix `env(ORDER_PREFIX,'TMX')`, `random_length=5` (hậu tố alphanumeric HOA).
- `rate_limits`: login 5, checkout 3, apply_coupon 10, search 30, send_otp 3 (/phút) — ⚠️ THỰC TẾ mới dùng `apply_coupon` (CartController) và `search` (SearchController) qua `RateLimiter::` facade trực tiếp; login/checkout/send_otp chưa nơi nào call.
- `cache`: home(featured_categories 600, flash_sale 300, best_sellers 600, banners 3600) · catalog(category_tree 3600, product_detail 300, **flash_sale 300**) · content(posts 1800, testimonials 3600) · settings 3600.
- `shipping`: default_fee 30000 (env SHIPPING_DEFAULT_FEE), **fast_fee 30000 (env SHIPPING_FAST_FEE)**, free_threshold env(SHIPPING_FREE_THRESHOLD, 300000), internal_carrier_name "Giao hàng nội bộ Thảo Mộc Farm".
- `upload`: max 2048KB, mimes jpeg/png/webp, thumbs 300/600/1200.
- `review`: require_verified_purchase=true, auto_approve=false.

### `home.php`
`limits` (featured_categories 8, coupons 8, best_sellers 4, herbal_tea 8, flash_sale 8) · `ttl` (600×4, flash_sale 300) · `flash_hooks` (urgent_percent 70, urgent_slots 10, deep_discount_percent 30, hot_sold_count 100 — ngưỡng text "Sắp cháy hàng/Giảm sâu/Bán chạy").

### `catalog.php`
`per_page=12` (category) · `all_products_per_page=8` · `sort_options` bestsell/newest/price_asc/price_desc · `price_ranges` (0-100k / 100-250k / 250k+) · `rating_filters [3,4,5]` · `seo_file = base_path('data/catalog-seo.json')`.

### `coupons.php`
`messages` (invalid/disabled/expired/not_eligible/exhausted/applied — text validate), `desc_templates` (fixed/percent/shipping/default — mô tả tự sinh khi coupon trống description), `labels` (min_order_prefix, no_min_cart/no_min_public, expiry_prefix, no_expiry, max_discount_suffix).

## 13. Helpers — `app/Support/helpers.php` (autoload files của composer.json)

- `group_version_key(string $group): string` → `thaomoc:group_version:{group}`.
- `remember_group(string $group, string $key, int $ttl, Closure $cb): mixed` → key thực `{group}:v{version}:{key}` (thay Cache::tags trên driver file).
- `bump_group_version(string $group): int` → tăng version, mọi key nhóm vô hiệu tức thì.
- `format_vnd(int): string` → `800.000₫`.
- `format_number_compact(int): string` → `3.1k`, `1.2m`.

## 14. Database Export for AI

`php artisan ai:export-database` (trong `src/`) → `PROJECT_CONTEXT/ai-database/INDEX.md`, `tables/<bảng>.md`, `data/<bảng>.jsonl`. Ý nghĩa + cách đọc: xem PROJECT_CONTEXT/README.md. Lưu ý bug lặp dòng INDEX hiện tại (mục Bẫy 01-DATABASE.md §34.10).

---

## 15. Bẫy đã biết — Không được phá

1. **Catch-all `/{slug}`** phải CUỐI cùng routes/web.php; route mới đặt trước.
2. **Route names** luôn prefix `web.` — không đổi thành `product.show`...
3. **Không FK constraint** — Service tự đảm bảo toàn vẹn.
4. **coupon_products/coupon_categories** không timestamps.
5. **Cache driver file** — cấm `Cache::tags()`, dùng remember_group/bump_group_version.
6. **Frontend contracts**: `#cartBadge` (selector JS rộng: `.js-cart-count, .cart-count`), `.add-cart`, `#toast`, `meta[name="csrf-token"]`, `[data-drawer-open]`/`[data-drawer-close]`, `window.updateCartCount(n)`, importmap `@tm/*`, 2 entry `app.js`/`cart.js`. Đổi tên = cập nhật đồng bộ toàn bộ Blade/JS.
7. **Money int VND** chỉ format ở view bằng `format_vnd()`; không format trước khi vào Service/DB.
8. **FlashSalePriceService** là nguồn giá deal duy nhất — mọi chỗ hiển thị giá flash phải đi qua nó; sửa deal → `flush()`.
9. **Checkout path thật**: stock_movements `export` qty âm; không payments/order_status_histories; order_number hậu tố alphanumeric HOA.
10. **Rate limit** implement bằng `RateLimiter::` facade trong controller (key `apply_coupon|ip|user`, `search_suggest|ip`), không middleware `throttle:`.
11. **`PostFactory` chưa tồn tại** dù `Post.php` reference — cẩn thận khi test/factory.
12. **style.css**: không viết rule trực tiếp, không thêm @import font thứ 2, không đảo cascade 15 partials.

## 16. Core Contracts Summary

| Nhóm | Contract |
|---|---|
| Routes | `/{slug}` cuối file; prefix tên `web.` |
| Database | Không FK; pivot coupon không timestamps; tiền int unsigned; qty stock_movements signed |
| Cache | driver file; remember_group/bump_group_version; groups home/catalog/content/settings |
| Giá flash | flash_price=gốc gạch ngang; bán=round(flash_price×(100−%)/100); qua FlashSalePriceService |
| Checkout | TMX-YYYYMMDD-XXXXX (HOA); shipment internal; movement export âm |
| Frontend | #cartBadge/.add-cart/#toast/csrf-meta/drawer attrs/updateCartCount/importmap @tm |
| JS | input.value bằng JS → dispatchEvent('change'); app.js/cart.js giữ đường dẫn |
| Export | `php artisan ai:export-database` → `PROJECT_CONTEXT/ai-database/` |

> Nguyên tắc: thay đổi code phải giữ nguyên contracts trên trừ khi yêu cầu rõ ràng; nếu buộc phá, cập nhật ĐỒNG BỘ mọi thành phần phụ thuộc rồi mới hoàn tất.