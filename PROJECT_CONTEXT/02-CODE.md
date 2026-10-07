# 02 — CODE (Mộc Xanh)

> Trích từ code THỰC TẾ trong `src/` (đối chiếu ngày 2026-10-07). Laravel `^13.17`, PHP `^8.3`, `declare(strict_types=1)` phổ biến.

---

## 1. Artisan Commands

| Lệnh | File | Tác dụng |
|---|---|---|
| `php artisan ai:export-database` | `app/Console/Commands/AiExportDatabase.php` | Xuất cấu trúc + TOÀN BỘ dữ liệu mọi bảng ra `PROJECT_CONTEXT/ai-database/` (`INDEX.md`, `tables/<bảng>.md`, `data/<bảng>.jsonl`). Chỉ SELECT. Chunk 500, sample 50 dòng/bảng, cap 1.5MB/file md. |
| `php artisan admin:import-locations` | `app/Console/Commands/ImportVietnamLocations.php` | Import `data/vietnam_2_levels_v2.json` → `provinces` + `wards` qua `VietnamLocationService`. Options `--path=`, `--fresh`, `--dry-run`. |

`ai:export-database` options: `--connection=` (mặc định `.env`), `--output=` (mặc định `dirname(base_path()).'/PROJECT_CONTEXT/ai-database'`, fallback `base_path('PROJECT_CONTEXT/ai-database')`), `--include-framework` (thêm cache/jobs/sessions/migrations...). Ngoài ra `routes/console.php` có closure command `inspire` (stock Laravel). Chưa có command bump cache.

## 2. Models — `app/Models` (namespace `App\Models`) — 31 models

`Address, Banner, Cart, CartItem, Category, Coupon, CouponUsage, NewsletterSubscriber, Order, OrderItem, OrderStatusHistory, Payment, Post, PostCategory, Product, ProductImage, ProductVariant, Promotion, PromotionProduct, Province, Review, ReviewVote, SearchTerm, Setting, Shipment, StockMovement, Store, Testimonial, User, Ward, Wishlist`

Không model nào set `$table` — dùng quy ước plural-snake mặc định. ⚠️ 26/27 model gắn `#[UseFactory(*Factory::class)]` nhưng factory CHƯA tồn tại (chỉ `UserFactory` có); `Province`/`Ward`/`ReviewVote` không reference factory.

### Product
Scopes: `active()` (status active + published_at ≤ now), `featured()`, `bestSeller()` (orderByDesc sold_count), `search(?string)` (FULLTEXT `MATCH(name) AGAINST(? IN BOOLEAN MODE)` OR LIKE fallback). Relations: `category`, `variants`, `images`, `reviews`, `wishlists`, `promotionProducts`; HasOne `defaultVariant()` (ofMany is_default), `coverImage()` (ofMany is_cover). Methods: `url()` → `/san-pham/{slug}`, `formattedPriceMin()`, `incrementViews()`. Casts: `published_at` datetime, `seo` array, `is_featured` bool, **`specs_json` array** (mới).

### Category
`scopeActive()`, `scopeFeatured()`, `url()` → `/danh-muc/{slug}`, self-relation `parent()`/`children()` (parent_id), `products()`. Cast `is_featured` bool.

### Coupon
Scopes: `active()`, `valid()` (nullable-aware starts_at/expires_at với now()), `displayable()` (usage-limit subquery). Relations: `products()` (belongsToMany qua coupon_products), `categories()` (belongsToMany qua coupon_categories), `usages()`. Casts: `starts_at`, `expires_at`.

### Review (đã mở rộng)
Fillable thêm `ip_address, customer_name, customer_phone`; `user_id` giờ nullable. Casts `images` array, `is_verified` bool. Relations: `product()`, `user()`, **`votes(): HasMany(ReviewVote)`**. Scope `approved()`.

### ReviewVote (MỚI)
Fillable `review_id, ip_address`; relation `review(): BelongsTo(Review)`. UNIQUE `(review_id, ip_address)` ở DB.

### Province / Ward (MỚI)
- `Province`: fillable `code,name,division_type,codename,phone_code`; `wards(): HasMany(Ward,'province_code','code')`; scope `ordered()`; `shortName()`.
- `Ward`: fillable `code,name,division_type,codename,province_code,province_name`; `province(): BelongsTo(Province,'province_code','code')`; scopes `ofProvince($code)`, `ordered()`; `shortName()`.
- Join theo `code` (KHÔNG phải id). KHÔNG có model `District`.

### Banner
`scopeActive()`, `scopePosition(string)`, `imageUrl()`; casts start_at/end_at.

### Promotion
`scopeActive()`, `scopeFlashSale()`, `scopeCurrentlyRunning()`; casts start_at/end_at; `products(): HasMany(PromotionProduct)`.

### PromotionProduct
`slotsLeft(): int`, `soldPercent(): int`; relations `promotion()`, `product()`, `variant()`.

### Post / PostCategory
`Post::scopePublished()`, relation `category()` (FK `post_category_id`), cast `published_at`, `url()`, `incrementViews()`. ⚠️ `#[UseFactory(PostFactory::class)]` trỏ factory CHƯA tồn tại. `PostCategory::scopeActive()`, `posts(): HasMany(Post,'post_category_id')`.

### User
Extends `Illuminate\Foundation\Auth\User`, `#[Hidden(['password','remember_token'])]`. Role string (`admin/staff/customer`); `isStaff()`, `isAdmin()`; relations addresses/orders/wishlists/reviews + `cart()` (HasOne). Casts `email_verified_at` datetime, `password` hashed.

### Order
⚠️ Khai báo casts **2 lần** (property `$casts` + method `casts()`). Relations: `user()`, `items()`, `coupon()`, `payment()` (HasOne), `shipment()` (HasOne), `statusHistories()`. Method `formattedTotal()`. Fillable có `coupon_code_snapshot`.

### SearchTerm / Setting
`SearchTerm::track(string)` (updateOrCreate, hits+1, min 2 ký tự). `Setting::get($key,$default)` (json-decode), `Setting::set($key,$value,$group)`.

Date casts tổng hợp: Coupon(starts_at, expires_at) · Promotion(start_at, end_at) · Banner(start_at, end_at) · Product(published_at) · Post(published_at) · Order(paid_at, cancelled_at) · Payment(paid_at) · Shipment(shipped_at, delivered_at).

## 3. Enums — `app/Enums` — 11 backed string enums

| Enum | Values | Ghi chú |
|---|---|---|
| `OrderStatus` | new, confirmed, packing, shipping, delivered, cancelled, returning | `label()` tiếng Việt + `next()` (luồng chuyển trạng thái hợp lệ) |
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

Không có enum cho division_type của Province/Ward.

## 4. DTOs — `app/DTOs` — readonly promoted properties

- **ProductViewDTO**: `id`, `slug`, `name`, `sku`, `subtitle`, `description`, `price:int`, `old_price:?int`, `discount_percent:int`, `avg_rating:float`, `review_count`, `sold_count`, `stock`, `image`, `meta_description`, `category:?CategoryViewDTO`, `flashSale:?array`.
- **CategoryViewDTO**: `name`, `url`.
- **RelatedProductDTO**: `product_id`, `variant_id:?int`, `url`, `image`, `name`, `price`, `old_price`, `discount_percent`, `avg_rating`, `sold_count`.
- **ReviewViewDTO**: `customer`, `rating`, `content`, `created_at`, `is_verified:bool` + `fromArray()` + magic `__get()`.

## 5. Services — `app/Services`

### CartService (`CartService.php`)
Constructor inject `CouponService` + `Promotion\FlashSalePriceService`. Methods: `getOrCreateCart(): Cart` (member theo user_id + merge guest; guest theo cookie `cart_token`, tạo mới Str::uuid), `mergeGuestCart(Cart)`, `addToCart(cartId, productId, variantId, qty=1): CartItem` (check tồn kho, throw `\Exception`), `updateCartItem(cartId,itemId,qty): bool`, `removeItem(cartId,itemId): bool`, `getCartDetails(int): array` (giá unit qua FlashSalePriceService; trả items/subtotal/total_items/total_qty), `getCartSummary(int): array` (delegate `Cart\CartTotals::compute`; keys subtotal/discounted_subtotal/discount/shipping_fee/total/total_qty/item_count/applied), `getCartItemCount(int): int`, `calculateShippingFee(subtotal): int` (= standard), `calculateShippingFeeForMethod(method, subtotal)`: **fast → luôn `fast_fee`; standard → 0 nếu subtotal ≥ free_threshold, ngược lại default_fee**.

### Sub-service `Cart/CartTotals::compute(subtotal, ?applied, goodsDiscount, callable $shippingFeeFor): array`
Pure function: goodsDiscount trừ trước → ship tính trên subtotal đã trừ → coupon type=shipping miễn toàn bộ ship → total = discounted + fee − shipDiscount. Trả `{discounted_subtotal, shipping_fee, shipping_discount, discount, total}`.

### CatalogService (`CatalogService.php`)
Trait `Catalog\ProductQueryFilters`; const `CATEGORY_PER_PAGE=12`, `SORT_OPTIONS`, `PRICE_RANGES`. Inject `FlashSalePriceService`. Public: `getProductDetail(slug): array` (cache nhóm `catalog` key `product_detail:{slug}`, flash pricing áp NGOÀI cache, hydrate DTO), `getProductReviews(productId): array` (cache nhóm `review` key `product_reviews:{id}` TTL 600; trả `{reviews, stats}`), `getCategoryShow(slug, rawFilters): ?array`, `getAllProducts(rawFilters): array` (per page `catalog.all_products_per_page`=8), `searchProducts(keyword, rawFilters, ?limit): array` (limit>0 → suggest mode, không phân trang). Private: `seoFile()` đọc `config('catalog.seo_file')`.
Sub-services (`Catalog/*`, static/stateless):
- `ProductQueryFilters` (TRAIT) — `normalizeCategoryFilters()`, `applyListingFilters()` (price OR / rating ≥ / sort theo `config/catalog.php`).
- `ProductListingMapper::toCard(Product, ?FlashSalePriceService)` — map product → card array (có `is_flash_sale`).
- `ProductDetailFetcher::fetch(slug)` (PDP thô, gồm `specs` từ `specs_json`, relatedProducts 4) / `::fetchReviews(productId)` (approved limit 20, `withCount('votes')`, stats avg/by_star, `initials()`).
- `ProductDetailHydrator::hydrate(cached, reviewData, ?flashPricing)` — ghép DTO + flash block; const `SPEC_LABELS` (11 key specs_json → nhãn tiếng Việt).
- `BreadcrumbBuilder::withCurrent(crumbs)` / `::schema(crumbs)` — breadcrumb + JSON-LD.

### CheckoutService (`CheckoutService.php`)
Inject CartService + CouponService. `const SHIPPING_METHODS = ['fast','standard']`.
- `getCheckoutData(cartId, shippingMethod='standard'): array` — trả [] khi giỏ trống.
- `calculateShippingFee(method, subtotal)` — delegate `CartService::calculateShippingFeeForMethod`.
- `normalizeShippingMethod(method)` — whitelist, mặc định standard.
- `processCheckout(cartId, validated): Order` — resolve coupon LẦN 2 chống race; `DB::transaction` ghi: **orders** (COD, status NEW, payment PENDING, đủ coupon_code_snapshot), **order_items** (snapshot; price đã gồm flash discount), **shipments** (internal/pending), **stock_movements** (`type=EXPORT`, qty ÂM, ref order) sau khi trừ stock bằng conditional UPDATE nguyên tử `where stock >= qty` (affected=0 → exception "không đủ tồn kho"), coupon `recordUsage`, xóa CartItem, `removeAppliedCoupon()`. ⚠️ KHÔNG tạo payments, KHÔNG ghi order_status_histories, KHÔNG dùng order_reserve.
- `generateOrderNumber(): string` private — `{prefix}-{Ymd}-` + `strtoupper(Str::random(5))`, loop until unique.

### CouponService (`CouponService.php`)
`const SESSION_KEY='cart_coupon_code'`. `findByCode(code)` (case-insensitive), `eligibleSubtotal(cartId, coupon)` (theo pivot products/categories hoặc toàn giỏ), `validateForCart(coupon, cartId, ?userId): array{ok,reason,eligible}`, `calculateDiscount(coupon, eligibleSubtotal, shippingFee): int` (fixed/percent có max_discount/shipping), `applyToCart(code, cartId, ?userId): array{success,message,code}` (session-based), `resolveAppliedCoupon(cartId, ?userId): ?array` (re-validate, auto-remove khi invalid), `removeAppliedCoupon()`, `getAvailableCoupons(cartId, ?userId)`, `applyCoupon(code, cartTotal, ?userId)` (legacy), `recordUsage(couponId, ?userId, orderId, discountAmount)`, `getPublicCoupons(limit=8)`. Text qua `Coupon/CouponTexts` + `config/coupons.php`.

### Coupon/CouponTexts (static)
`message(key, replace)`, `autoDescription(Coupon)`, `minOrderLabel(min, fallbackKey)`, `expiryLabel(?expiresAt)`.

### HomeService (`HomeService.php`)
Inject CouponService + FlashSalePriceService. `featuredCategories()`, `flashSale(): ?array` (KHÔNG tham số; keys promotion_id/promotion_name/ends_at_unix/is_ended/sold_today/urgent_count/items; vẫn render sau khi kết thúc — countdown roll 24h), `homeCoupons()` (→ getPublicCoupons), `bestSellers()` (flash-aware), `herbalTea()` (category slug `tra-hoa-thao-moc`). Private `flashHookText()` (badge hot|deep|sell|new theo `home.flash_hooks`). Limits/TTL đọc `config/home.php`. Cache nhóm `home`.

### BlogService (`BlogService.php`)
`const TTL=1800`. `homeTips()` (4 bài mới nhất, cache `home` key `tips`), `categories()` (post_categories active + posts_count, cache `content`), `paginate(?categorySlug, perPage=9): LengthAwarePaginator` (cache `content`), `show(slug): ?array` (KHÔNG cache — trả post + relatedPosts 4 + latestPosts 5 + categories + breadcrumbs; view_count tăng từng lượt), `indexSeoMeta(?categorySlug)`.

### MenuService (`MenuService.php`)
`drawerCategories(): array` — categories active + withCount products active (chống N+1), limit `home.limits.featured_categories`, cache nhóm `content` key `drawer_categories`; try/catch (Throwable) trả [] (drawer tự ẩn).

### ReviewService (`ReviewService.php`) — MỚI
`const MAX_IMAGES=5`, `IMAGE_FOLDER='assets/images/reviews'`.
- `hasPurchased(productId, ?userId, ?email, ?phone=null): bool` — 3 bậc: (1) user_id trên đơn delivered; (2) SĐT chuẩn hóa qua `Review\ReviewPhoneMatcher::findDeliveredOrder`; (3) email fallback.
- `alreadyReviewed(productId, ?userId, ip): bool` — trùng theo user_id HOẶC ip_address trên review pending/approved.
- `store(Product, Request, data): string` — tạo review; throw `\RuntimeException` (message tiếng Việt) khi: đã review, thiếu SĐT, không có đơn delivered (nếu `thaomoc.review.require_verified_purchase`=true). Ghi customer_name/customer_phone/images(json), is_verified=true, status theo `thaomoc.review.auto_approve`; `bump_group_version('review')`.
- `markHelpful(reviewId, ip): int` — `ReviewVote::insertOrIgnore` (UNIQUE review_id+ip), trả count mới.
- `reply(Review, reply): Review` — set/clear `admin_reply`, bump cache `review`.
- private `storeImages(?files): array` — validate mime (`thaomoc.upload.allowed_mimes`) + size (`thaomoc.upload.max_size_kb`), move vào `public/assets/images/reviews/`.

### Review/ReviewPhoneMatcher (static) — MỚI
`normalize(?phone): ?string` (về `0xxxxxxxxx`; nhận `+84`/`0084`/`84`; 9–11 chữ số, sai → null), `findDeliveredOrder(productId, normalizedPhone): ?object` (đơn `delivered` chứa SP, khớp SĐT exact OR LIKE chèn `%` giữa các chữ số), `makeFileName(UploadedFile): string` (`rv_{Ymd}_{16hex}.{ext}`).

### VietnamLocationService (`VietnamLocationService.php`) — MỚI
`const CHUNK_SIZE=200`. `loadProvinces(path): array` (đọc JSON, strip BOM, `JSON_THROW_ON_ERROR`, assert shape province `code,name,codename,wards[]` + ward `code,name,codename,province_code`), `import(data, fresh=false): array{provinces,wards}` (1 transaction; từ chối nếu đang trong transaction mở hoặc thiếu bảng; truncate tùy `--fresh`; upsert chunk theo `code`; cuối cùng `ensureWardProvinceForeignKey()` — gắn FK `wards.province_code→provinces.code ON DELETE CASCADE`, chỉ MySQL).

### Promotion/FlashSalePriceService — NGUỒN SỰ THẬT DUY NHẤT cho giá deal
Quy ước: `flash_price`=giá gốc gạch ngang; giá bán=round(flash_price×(100−discount_percent)/100); percent=0 → suy từ giá niêm yết. Cache nhóm `catalog` (TTL `thaomoc.cache.catalog.flash_sale`=300). Không lọc end_at — promotion active vẫn trả deal, hết hạn do `is_ended` (view quyết). API: `currentDeals()`, `dealsByProduct()`, `dealFor(productId, ?variantId, isDefaultVariant=true)`, `priceFor(productId, ?variantId, basePrice, originalBase=0, isDefaultVariant=true): ?array` (null nếu deal price ≥ base), `currentPromotionMeta()` (countdown clamp ≤24h, roll now+24h khi ended), `pdpBlockFor(productId)`, `applyToDetailArray(cached)`, `productIdsWithDeals()`, `categoryIdsOfDeals()`, `flush()` (bump `catalog`).

### Cache groups thực dùng
`home`, `catalog`, `content`, `review`, `settings`. Bump bằng `bump_group_version()` (ReviewService tự bump `review`; FlashSalePriceService `flush()` bump `catalog`) — chưa có artisan command bump.

## 6. Controllers — `app/Http/Controllers/Web` — 9 controllers

Base `Controller.php` rỗng. Không có controller dir Admin/Api.

| Controller | Methods | Ghi chú |
|---|---|---|
| `HomeController` | `index` | inject HomeService+BlogService; view `web.home` với featuredCategories/flashProducts/coupons/bestSellers/herbalTeaProducts/tips |
| `ProductController` | `show(CatalogService, slug)`, `index(CategoryShowRequest, CatalogService)` | show: `increment('view_count')` nguyên tử; views `web.product` / `web.products` |
| `CategoryController` | `show(CategoryShowRequest, CatalogService, slug)` | `abort_if($data===null,404)`; view `web.category` |
| `SearchController` | `index(CategoryShowRequest)`, `suggest(Request): JsonResponse` | inject CatalogService; index log `SearchTerm::track`; suggest: RateLimiter `thaomoc.rate_limits.search`(30)/phút/IP, q<2 ký tự trả rỗng, limit 6, `{items,total,more_url}` |
| `CartController` | `index`, `add`, `buyNow`, `update`, `remove`, `count`, `applyCoupon`, `removeCoupon` | inject CartService+CouponService; add/buyNow dùng AddToCartRequest; buyNow trả `redirect`→checkout; set cookie `cart_token` 43200 phút cho guest; applyCoupon RateLimiter `thaomoc.rate_limits.apply_coupon`(10)/phút key `apply_coupon|{ip}|{userId|guest}` |
| `CheckoutController` | `index(Request)`, `store(CheckoutRequest)`, `success(Request, order_number)` | inject CheckoutService+CartService; index giữ `old('shipping_method')`, giỏ trống redirect cart; store: processCheckout → redirect success + forget cookie guest; catch → back withInput + error |
| `BlogController` | `index`, `category(slug)`, `show(slug)` | inject BlogService+HomeService; views `web.blog.index`/`web.blog.show`; category throw NotFoundHttpException khi slug lạ; show `incrementViews()` + suggestedProducts (3 bestSellers) |
| `PageController` | `orderGuide`, `returnPolicy`, `privacyPolicy`, `terms`, `about`, `contact` | inject HomeService; views `web.pages.*` (nội dung hardcode trong Blade); một số trang kèm bestSellers/herbalTea |
| `ReviewController` | `store(StoreReviewRequest, slug)`, `helpful(Request, reviewId)`, `reply(Request, reviewId)` | MỚI; inject ReviewService; **tất cả trả JsonResponse**. store: 404 nếu slug không active, RateLimiter 3/phút/IP key `store_review|{ip}` (429), `\RuntimeException`→422, Throwable→500. helpful: RateLimiter 20/phút/IP key `review_helpful|{ip}`, trả `{success, helpful_count}`. reply: yêu cầu `user()->isStaff()` else 403 (không Gate/Policy), validate inline `admin_reply nullable|string|max:1000` |

## 7. FormRequests — `app/Requests` (namespace `App\Requests`) — 6 requests

Tất cả `authorize(): true` + `messages()` tiếng Việt.

- **AddToCartRequest**: `product_id required|integer|exists:products,id`; `variant_id required|integer|exists:product_variants,id`; `qty required|integer|min:1|max:99`.
- **UpdateCartRequest**: `qty` 1..99.
- **ApplyCouponRequest**: `prepareForValidation` uppercase+trim; `code required|string|min:3|max:50|regex:/^[A-Z0-9\-]+$/`.
- **CheckoutRequest**: name ≤255 required · phone `regex:/^[0-9]{9,11}$/` · email nullable email ≤255 · province required ≤255 · district required ≤255 · ward nullable ≤255 · address required ≤255 · `shipping_method in:fast,standard` · `payment_method in:cod` · note nullable ≤1000.
- **CategoryShowRequest**: `sort` in(bestsell,newest,price_asc,price_desc) · `price[]` in('0-100','100-250','250-') · `rating` nullable in(3,4,5) · `cat[]` integer exists:categories,id · `page` 1..10000.
- **StoreReviewRequest** (MỚI): `rating required|integer|min:1|max:5` · `content required|string|min:10|max:1000` · `name nullable|string|max:100` · **`phone required|string|regex:/^[0-9+ ]{9,15}$/`** (SĐT là link verified-purchase, email không bắt buộc) · `email nullable|email:rfc|max:150` · `images nullable|array|max:5` · `images.* file|mimes:jpg,jpeg,png,webp|max:{thaomoc.upload.max_size_kb}`. `prepareForValidation`: coerce rating string ("3.0000"/NaN từ rateyo) → int 1..5, trim fields, lọc file rỗng/không hợp lệ.

## 8. Routes — `routes/web.php` (THỨ TỰ THẬT, catch-all bắt buộc cuối)

| # | Method | URI | Name | Action |
|---|---|---|---|---|
| 1 | GET | `/` | `web.home` | HomeController@index |
| 2 | GET | `/tat-ca-san-pham` | `web.products.index` | ProductController@index |
| 3 | GET | `/tim-kiem` | `web.search.index` | SearchController@index |
| 4 | GET | `/tim-kiem/goi-y` | `web.search.suggest` | SearchController@suggest |
| 5 | GET | `/danh-muc/{slug}` `[a-z0-9\-]+` | `web.category.show` | CategoryController@show |
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
| 17 | GET | `/san-pham/{slug}` `[a-z0-9\-]+` | `web.product.show` | ProductController@show |
| 18 | POST | `/san-pham/{slug}/danh-gia` `[a-z0-9\-]+` | `web.product.reviews.store` | ReviewController@store |
| 19 | POST | `/danh-gia/{review}/huu-ich` `[0-9]+` | `web.review.helpful` | ReviewController@helpful |
| 20 | POST | `/danh-gia/{review}/phan-hoi` `[0-9]+` | `web.review.reply` | ReviewController@reply |
| 21 | GET | `/huong-dan-dat-hang` | `web.page.order-guide` | PageController@orderGuide |
| 22 | GET | `/chinh-sach-doi-tra` | `web.page.return-policy` | PageController@returnPolicy |
| 23 | GET | `/chinh-sach-bao-mat` | `web.page.privacy` | PageController@privacyPolicy |
| 24 | GET | `/dieu-khoan-su-dung` | `web.page.terms` | PageController@terms |
| 25 | GET | `/gioi-thieu` | `web.page.about` | PageController@about |
| 26 | GET | `/lien-he` | `web.page.contact` | PageController@contact |
| 27 | GET | `/cam-nang` | `web.blog.index` | BlogController@index |
| 28 | GET | `/cam-nang/category/{slug}` `[a-z0-9\-]+` | `web.blog.category` | BlogController@category |
| 29 | GET | `/cam-nang/{slug}` `[a-z0-9\-]+` | `web.blog.show` | BlogController@show |
| 30 | GET | `/{slug}` `[a-z0-9\-]+` | `web.category.alias` | CategoryController@show — **CUỐI CÙNG** |

⚠️ Route catch-all tên `web.category.alias` (doc cũ ghi `web.category.pretty` — tên thật hiện tại là `.alias`). KHÔNG có route province/ward/location (dữ liệu hành chính chưa nối vào checkout). `bootstrap/app.php` khai health `GET /up`; `shouldRenderJsonWhen` cho `api/*` hoặc expectsJson. Chưa đăng ký middleware throttle (rate limit gọi trực tiếp trong controller).

## 9. Views — `resources/views`

Layout: `<x-layouts.app>` (`components/layouts/app.blade.php`) — props `title`(default 'Mộc Xanh')/`seoDescription`/`ogType`/`ogImage`/`bodyClass`/`hideCatnav`/`hideFloatnav`/`showBackToTop`; meta csrf-token + viewport-fit=cover + theme-color `#2e7d4f`, SEO/OG (og:locale vi_VN), canonical `url()->current()`, font preload (lora-700 + be-vietnam-pro-400), `style.css`. **4 stack**: `vendorStyles` (head, trước importmap — lightgallery + rateyo CSS push riêng), `styles` (head), `vendorScripts` (body, TRƯỚC app.js để `window.lightGallery`/rateyo tồn tại), `scripts` (body, sau loader). Chứa **importmap**, header/footer/floatnav/drawer, `.toast#toast`, 2 loader module.

Pages (`web/`): `home`, `products`, `category`, `search`, `product` (push vendorStyles lightgallery+rateyo & vendorScripts jQuery+lightgallery UMD+plugins+rateyo), `cart` (@push scripts), `checkout` (`:hide-catnav :hide-floatnav`), `checkout-success` (render `address_snapshot['ward']`), `blog/index`, `blog/show`, `pages/{about,contact,order-guide,privacy-policy,return-policy,terms}`. Legacy: `home.blade.php` (gốc), `welcome.blade.php`.

Components (`components/`):
- Root: `header`, `footer`, `drawer`, `floatnav`, `back-to-top`. (KHÔNG có `components/common/`.)
- `layouts/`: `app`.
- `sections/` (flat, không có `sections/home/`): `hero`, `usp`, `collections`, `best-sellers`, `flash-sale`, `herbal-tea`, `coupons` (@push scripts), `region`, `tips`, `stats`, `testimonials`, `trust`, `newsletter`.
- `product/`: `gallery`, `info`, `buybar`, `tabs` (chứa block `.rv-zone` review: rateyo `#rvRateyo`, filter, helpful, reply), `related`, `schema`, `flash-block`, **`review-item` (MỚI)**.
- `category/`: `toolbar`, `chips`, `filters`, `filter-groups`, `filter-drawer`, `pagination`, `seo-text`.
- `ui/`: `product-card`, `flash-card`, `category-card`, `coupon-card`, `breadcrumb`, `pagination`.
- `blog/`: `suggest-product`.

`review-item.blade.php` (`@props(['review'])`): `.review[data-star]`, avatar initials, `.rv-star`, `.review__verified` ("✔ Đã mua"), `.review__images` (max 5), `.review__reply` (admin_reply), `.rv-helpful.js-rv-helpful[data-review-id]`, và (chỉ staff, gate `auth()->user()->isStaff()`) `.rv-reply-btn.js-rv-reply`, `.rv-reply-box#rvReplyBox-{id}`, `.js-rv-reply-text/-save/-clear` → POST `web.review.reply`.

⚠️ `app/View/Components/` chỉ có 2 thư mục RỖNG (`Category/`, `Ui/`) — KHÔNG có PHP component class; component Blade nằm hết trong `resources/views`. `AppServiceProvider` register()/boot() rỗng (không view composer/share/binding).

Quy tắc: component TỰ ẨN khi data rỗng — không render "không có dữ liệu".

## 10. CSS — `public/assets/css`

Entry `style.css` = map @import **15 partials** theo đúng cascade, KHÔNG đảo thứ tự, KHÔNG viết rule trực tiếp:
`01-base, 02-header, 03-hero, 04-home, 05-product-card, 06-category-page, 07-product-detail, 08-cart, 09-checkout, 10-success, 11-coupon, 12-layout-extras, 13-static-page, 14-flash-sale, 15-blog`.

`07-product-detail.css` là **import hub** cho 7 sub-partial (KHÔNG khai trong style.css, mà `@import` bên trong 07, thêm cuối nhóm): `07a-pdp-layout, 07b-pdp-gallery, 07c-pdp-options, 07d-pdp-tabs, 07e-pdp-reviews (MỚI), 07f-pdp-lightgallery (MỚI), 07g-pdp-gallery-fx (MỚI)`. Tổng 22 file trong `partials/`.

Font import nằm TRONG `01-base.css` (`@import url('../fonts.css')`) — tuyệt đối không khai lại ở style.css. Mobile-first, breakpoint `max-width`. Vendor lightgallery + rateyo CSS nạp bằng `<link>` riêng qua stack `vendorStyles` (view nào push mới tải).

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
  "@tm/cart-add": "assets/js/cart-add.js",
  "@tm/review": "assets/js/app-review.js"
}}
```

Hai loader `type="module"`:
- `app.js`: static `import '@tm/core'` + dynamic import có điều kiện: `@tm/ui` (`#burgerBtn` hoặc `#filterGroups`+`#filterDrawerBody`), `@tm/backtotop` (`#backToTop`), `@tm/product` (`.countdown[data-ends]` | `[data-pd-flash]` | `.pd-thumbs` | `[role="tab"]` | `#buyNow` | `#buyNowMobile`), **`@tm/review` (`.rv-zone`)**, `@tm/search` (`#searchForm`+`#searchInput`+`#searchSuggest`).
- `cart.js`: static `@tm/cart-badge` (định nghĩa `window.updateCartCount` + GET `/gio-hang/count`) + conditional `@tm/cart-add` (`.add-cart`).

| Module | Vai trò | Kích hoạt khi có |
|---|---|---|
| `app-core.js` | helpers `q, qa, money, reduceMotion, toast(#toast+class show)`; chặn `a[href="#"]`/demo-link; reveal `.reveal→is-in`; count-up `[data-count]` | luôn |
| `app-ui.js` | burger drawer `[data-drawer-open]/[data-drawer-close]→is-open` + Esc + body `.is-locked`; clone `#filterGroups→#filterDrawerBody` | `#burgerBtn` / filter drawer |
| `app-backtotop.js` | `#backToTop` hysteresis (>160 hiện, <60 ẩn), cuộn rAF easeInOutCubic | `#backToTop` |
| `app-product.js` | hub import 8 submodule `product/*` (xem dưới) | marker PDP/home |
| `app-search.js` | ≥2 ký tự debounce 300ms → GET `/tim-kiem/goi-y`, dropdown ↑↓/Enter/Esc, spotlight `#spotlightOverlay`, typing placeholder | `#searchForm`+`#searchInput`+`#searchSuggest` |
| `app-review.js` (MỚI) | rateyo star widget `#rvRateyo`, AJAX submit review (multipart + ảnh), filter sao client-side, nút "Hữu ích" `.js-rv-helpful`, admin reply `.js-rv-reply*`; dùng `toast()` + `meta[name="csrf-token"]`; rateyo 2.3.4 self-host | `.rv-zone[data-product-slug]` |
| `cart-badge.js` | badge `.js-cart-count, .cart-count` (trong đó có `#cartBadge`), pulse, API global `window.updateCartCount(n)`, init fetch `GET /gio-hang/count` | cart.js import trước |
| `cart-add.js` | delegation `.add-cart`: đọc `input[name="variant_id"]:checked` (fallback `variant`), `input[name="qty"]` → POST `/gio-hang/them` + header `X-CSRF-TOKEN`; debounce click; BỎ QUA nút trong `.product-grid` không có variant | `.add-cart` |

Submodule `product/*` (8):
- `countdown.js` — flash H:M:S (`.countdown[data-ends]` unix giây, server clamp ≤24h; `#cdH/M/S`, `#pdCdH/M/S`).
- `gallery.js` — thumbs `.pd-thumbs`, prev/next `[data-pd-nav]`, swipe, 2-layer FX `.pd-stage__fx`; set `window.__pdGallerySuppressClick`.
- `lightgallery.js` — zoom `[data-pd-zoom="stage"]` (dynamic mode, vendor self-host).
- `share.js` — `[data-share-native]`, `[data-copy-url]`.
- `tabs.js` — ARIA `[role="tab"]` + panels.
- `spec-sync.js` — sync bảng "Quy cách & giá bán" `[data-spec-table]` theo radio `name="variant_id"` (fallback `variant`), `.spec-tag`, `#panel-spec`.
- `buy-now.js` — `#buyNow`/`#buyNowMobile` → POST `/gio-hang/mua-ngay` → `window.updateCartCount` → redirect `/thanh-toan`.
- `description-collapse.js` — "Xem thêm" `[data-desc-collapse]`, `#panel-desc`, `.is-collapsed/.is-foldable`.

Vendor (`public/assets/vendor/`): `lightgallery/*` (UMD + plugins zoom/thumb/pager/share/autoplay/hash/fullscreen), `rateyo/*` (jquery.rateyo 2.3.4 + CSS), `jquery.min.js`, `star-rating/*`. Nạp qua stack `vendorStyles`/`vendorScripts` ở `web/product.blade.php`.

**Bẫy JS:** importmap key ↔ `import '@tm/...'` phải khớp, module mới khai cả importmap (VD `@tm/review`); `window.updateCartCount` là contract (Buy Now + script inline products/category/search) — không đổi tên; giữ nguyên đường dẫn `app.js`/`cart.js`; `vendorScripts` phải chạy TRƯỚC `app.js`; set `input.value` bằng JS phải `dispatchEvent(new Event('change'))`; loader `type=module` mặc định defer. **Chưa có JS cascading select tỉnh/quận** — checkout dùng `<select>` hardcode.

## 12. Config — `src/config` (project-specific: thaomoc, home, catalog, coupons)

### `thaomoc.php`
- `order`: prefix `env(ORDER_PREFIX,'TMX')`, `random_length=5`.
- `rate_limits`: login 5, checkout 3, apply_coupon 10, search 30, send_otp 3 (/phút, env `RATE_LIMIT_*`) — ⚠️ THỰC TẾ mới dùng `apply_coupon` (CartController) và `search` (SearchController) qua `RateLimiter::` facade; login/checkout/send_otp chưa nơi nào call. ReviewController hardcode 3/phút (store) và 20/phút (helpful) — KHÔNG đọc config.
- `cache` (giây): home(featured_categories 600, flash_sale 300, best_sellers 600, banners 3600) · catalog(category_tree 3600, product_detail 300, **flash_sale 300**) · content(posts 1800, testimonials 3600) · settings 3600.
- `shipping`: default_fee 30000 (env SHIPPING_DEFAULT_FEE), **fast_fee 30000 (env SHIPPING_FAST_FEE)**, free_threshold env(SHIPPING_FREE_THRESHOLD, 300000), internal_carrier_name "Giao hàng nội bộ Thảo Mộc Farm".
- `upload`: max_size_kb env(UPLOAD_MAX_SIZE_KB, 2048), allowed_mimes jpeg/png/webp, thumbnails small 300/medium 600/large 1200.
- **`review` (MỚI)**: `require_verified_purchase=true`, `auto_approve=false`.

### `home.php`
`limits` (featured_categories 8, coupons 8, best_sellers 4, herbal_tea 8, flash_sale 8) · `ttl` (600×4, flash_sale 300) · `flash_hooks` (urgent_percent 70 "Sắp cháy hàng", urgent_slots 10, deep_discount_percent 30 "Giảm giá sâu", hot_sold_count 100 "Bán chạy").

### `catalog.php`
`per_page=12` · `all_products_per_page=8` · `sort_options` bestsell/newest/price_asc/price_desc · `price_ranges` (0-100: 0–100000 / 100-250: 100000–250000 / 250-: 250000–null) · `rating_filters [3,4,5]` · `seo_file = base_path('data/catalog-seo.json')`.

### `coupons.php`
`messages` (invalid/disabled/expired/not_eligible/exhausted/applied), `desc_templates` (fixed/percent/shipping/default), `labels` (min_order_prefix, no_min_cart/no_min_public, expiry_prefix, no_expiry, max_discount_suffix).

Không có config file riêng cho location (dữ liệu tỉnh/xã từ `data/vietnam_2_levels_v2.json` + DB).

## 13. Helpers — `app/Support/helpers.php` (autoload files của composer.json)

- `group_version_key(string $group): string` → `thaomoc:group_version:{group}`.
- `remember_group(string $group, string $key, int $ttl, Closure $cb): mixed` → key thực `{group}:v{version}:{key}` (thay Cache::tags trên driver file).
- `bump_group_version(string $group): int` → tăng version (`Cache::forever`), mọi key nhóm vô hiệu tức thì.
- `format_vnd(int): string` → `800.000₫`.
- `format_number_compact(int): string` → `3.1k`, `1.2m`.

## 14. Data files — `src/data`
- `catalog-seo.json` (6.2KB): key `seo_texts` per-slug (`thao-moc`, `tra-hoa-thao-moc`, `thit-gac-bep`, `gia-vi-tay-bac`, `mat-ong`) → heading/paragraph/subheading/tips. Đọc bởi CatalogService.
- `vietnam_2_levels_v2.json` (~940KB, MỚI): mảng provinces (`code,name,division_type,codename,phone_code` + `wards[]` `code,name,division_type,codename,province_code,province_name`). Import vào DB qua `admin:import-locations`; KHÔNG view/JS nào đọc trực tiếp.

## 15. Database Export for AI
`php artisan ai:export-database` (trong `src/`) → `PROJECT_CONTEXT/ai-database/INDEX.md`, `tables/<bảng>.md`, `data/<bảng>.jsonl`. Ý nghĩa + cách đọc: xem PROJECT_CONTEXT/README.md. Lưu ý bug lặp dòng INDEX (mục Bẫy 01-DATABASE.md §34.13).

---

## 16. Bẫy đã biết — Không được phá

1. **Catch-all `/{slug}`** (`web.category.alias`) phải CUỐI cùng routes/web.php; route mới đặt trước.
2. **Route names** luôn prefix `web.` — không đổi thành `product.show`...
3. **Không FK constraint** (trừ `wards→provinces`) — Service tự đảm bảo toàn vẹn.
4. **coupon_products/coupon_categories** không timestamps.
5. **Cache driver file** — cấm `Cache::tags()`, dùng remember_group/bump_group_version. Nhóm `review` bump bởi ReviewService.
6. **Frontend contracts**: `#cartBadge` (selector JS rộng: `.js-cart-count, .cart-count`), `.add-cart`, `#toast`, `meta[name="csrf-token"]`, `[data-drawer-open]`/`[data-drawer-close]`, `window.updateCartCount(n)`, importmap `@tm/*` (gồm `@tm/review`), 2 entry `app.js`/`cart.js`, `vendorScripts` chạy trước `app.js`. Đổi tên = cập nhật đồng bộ Blade/JS.
7. **Money int VND** chỉ format ở view bằng `format_vnd()`; không format trước khi vào Service/DB.
8. **FlashSalePriceService** là nguồn giá deal duy nhất — mọi chỗ hiển thị giá flash phải đi qua nó; sửa deal → `flush()`.
9. **Checkout path thật**: stock_movements `export` qty âm; không payments/order_status_histories; order_number hậu tố alphanumeric HOA.
10. **Rate limit**: `apply_coupon`/`search` qua `RateLimiter::` facade đọc `config('thaomoc.rate_limits.*')`; ReviewController hardcode 3/phút & 20/phút (không config); không middleware `throttle:`.
11. **`PostFactory` + 25 factory khác chưa tồn tại** dù model reference — cẩn thận khi test/factory (chỉ `UserFactory` có).
12. **style.css**: không viết rule trực tiếp, không thêm @import font thứ 2, không đảo cascade 15 partials; sub-partial 07a–07g `@import` bên trong `07-product-detail.css`.
13. **Review verified-purchase theo SĐT** (không phải email): `phone` required trong StoreReviewRequest; đối chiếu `orders.customer_phone` qua ReviewPhoneMatcher (chuẩn hóa + LIKE). `review_votes` UNIQUE `(review_id, ip_address)`.
14. **products.specs_json** render qua `ProductDetailHydrator::SPEC_LABELS` (11 key) — thêm key phải sửa cả hydrator.
15. **provinces/wards** join theo `code`, KHÔNG có `districts`; checkout vẫn hardcode `<select>` tỉnh/quận (chưa nối DB).
16. **`Order.php` casts 2 lần** (property + method) — sửa casts phải để ý cả hai.

## 17. Core Contracts Summary

| Nhóm | Contract |
|---|---|
| Routes | `/{slug}` (`web.category.alias`) cuối file; prefix tên `web.` |
| Database | Không FK (trừ wards→provinces); pivot coupon không timestamps; tiền int unsigned; qty stock_movements signed; review_votes UNIQUE(review_id,ip) |
| Cache | driver file; remember_group/bump_group_version; groups home/catalog/content/review/settings |
| Giá flash | flash_price=gốc gạch ngang; bán=round(flash_price×(100−%)/100); qua FlashSalePriceService |
| Checkout | TMX-YYYYMMDD-XXXXX (HOA); shipment internal; movement export âm |
| Review | verified-purchase theo SĐT (ReviewPhoneMatcher); rateyo `#rvRateyo`; helpful/reply JsonResponse; staff gate `isStaff()` |
| Frontend | #cartBadge/.add-cart/#toast/csrf-meta/drawer attrs/updateCartCount/importmap @tm (8 key)/vendorScripts trước app.js |
| JS | input.value bằng JS → dispatchEvent('change'); app.js/cart.js giữ đường dẫn |
| Export | `php artisan ai:export-database` → `PROJECT_CONTEXT/ai-database/`; `admin:import-locations` → provinces/wards |

> Nguyên tắc: thay đổi code phải giữ nguyên contracts trên trừ khi yêu cầu rõ ràng; nếu buộc phá, cập nhật ĐỒNG BỘ mọi thành phần phụ thuộc rồi mới hoàn tất.
