# 02 — CODE (models, enums, services, controllers, routes, views, CSS/JS — trích từ `src/`)

## Models (`app/Models`, namespace `App\Models`) — 28 model
Address, Banner, Cart, CartItem, Category, Coupon, CouponUsage, NewsletterSubscriber, Order, OrderItem, OrderStatusHistory, Payment, Post, PostCategory, Product, ProductImage, ProductVariant, Promotion, PromotionProduct, Review, SearchTerm, Setting, Shipment, StockMovement, Store, Testimonial, User, Wishlist.
Điểm đáng nhớ (đã xác minh trong code):
- `Product`: scopeActive/Featured/BestSeller/Search(?term), relations category/variants/images/reviews/wishlists/promotionProducts; `defaultVariant()` và `coverImage()` là HasOne lọc is_default/is_cover; `url()` trả `/san-pham/{slug}`; `incrementViews()`.
- `Category::url()` → `/danh-muc/{slug}`.
- `Coupon`: scopeActive, scopeValid (starts_at/expires_at nullable-aware với now), scopeDisplayable; belongsToMany products/categories + hasMany usages.
- `Banner`: scopeActive, scopePosition(string), imageUrl().
- `Promotion`: scopeActive, scopeFlashSale, scopeCurrentlyRunning.
- `PromotionProduct`: slotsLeft(), soldPercent(); quan hệ promotion/product/variant.
- `User`: role enum string, isStaff()/isAdmin(), hasMany addresses/orders/wishlists/reviews, hasOne cart.
- Casts datetime phổ biến: starts_at/expires_at (Coupon), start_at/end_at (Promotion, Banner), published_at (Product, Post).

## Enums (`app/Enums`, backed string) — 11 enum
| Enum | Values | Ghi chú |
|---|---|---|
| OrderStatus | new, confirmed, packing, shipping, delivered, cancelled, returning | có `label()` tiếng Việt + `next(): array` định nghĩa luồng chuyển trạng thái hợp lệ |
| OrderPaymentStatus | pending, paid, refunded | có label() |
| PaymentMethod | cod (bank_transfer đang bị comment) | chỉ COD hoạt động |
| CouponType | fixed, percent, shipping | có label() |
| PromotionType | flash_sale, campaign | |
| PromotionStatus | scheduled, active, ended, cancelled | |
| ProductStatus | draft, active, hidden | |
| BannerPosition | home_hero, home_mid, category, product | |
| ReviewStatus | pending, approved, hidden | |
| ShipmentCarrier | internal, ghn, ghtk, vtp | |
| StockMovementType | import, export, adjust, order_reserve, order_release | |

## DTOs (`app/DTOs`) — readonly promoted properties
- `ProductViewDTO`: id, name, sku, subtitle, description, price(int VND), old_price(?int), discount_percent, avg_rating(float), review_count, sold_count, stock, image, meta_description, category(?CategoryViewDTO).
- `CategoryViewDTO`: name, url. `RelatedProductDTO`: url, image, name, price, old_price, discount_percent, avg_rating, sold_count. `ReviewViewDTO`: customer, rating, content, created_at.

## Services (`app/Services`)
- **CartService**: getOrCreateCart, mergeGuestCart, addToCart, updateCartItem, removeItem, getCartDetails, getCartSummary, getCartItemCount, calculateShippingFee.
- **CatalogService**: getProductDetail, getProductReviews, getCategoryShow(?array), getAllProducts, searchProducts.
- **CheckoutService**: getCheckoutData, calculateShippingFee, processCheckout→Order. Toàn bộ ghi đơn chạy trong `DB::transaction`: tạo orders + order_items(snapshot) + payments + shipments + order_status_histories + stock_movements(order_reserve) + coupon_usages qua `recordUsage($couponId, auth()->id(), $order->id, $discountAmount)`; lưu `coupon_code_snapshot`.
- **CouponService**: findByCode, eligibleSubtotal, validateForCart, `calculateDiscount(Coupon, int $eligibleSubtotal, int $shippingFee): int`, applyToCart, resolveAppliedCoupon, removeAppliedCoupon, getAvailableCoupons, applyCoupon, recordUsage, getPublicCoupons.
- **HomeService**: featuredCategories, flashSale(?array), homeCoupons, bestSellers, herbalTea.
Cache group được dùng thực tế: `home`, `catalog`, `content`, `settings`, `review` (bump bằng helper, chưa có artisan command).

## Controllers (`app/Http/Controllers/Web`)
HomeController(index), ProductController(show, index), CategoryController(show), SearchController(index, suggest), CartController(index, add, update, remove, count, applyCoupon, removeCoupon — JSON), CheckoutController(index, store, success).

## Requests (`app\Requests` — namespace `App\Requests`, KHÔNG nằm trong Controllers/FormRequests)
- AddToCartRequest: product_id required exists:products,id; variant_id required exists:product_variants,id; qty 1..99.
- UpdateCartRequest: qty 1..99.
- ApplyCouponRequest: code uppercase+trim, 3..50, regex `/^[A-Z0-9\-]+$/`.
- CheckoutRequest: name ≤255; phone regex `/^[0-9]{9,11}$/`; email nullable; province/district/address required; ward nullable; shipping_method in:fast,standard; payment_method in:cod; note ≤1000. Messages tiếng Việt đầy đủ.
- CategoryShowRequest tồn tại (filter/sort cho trang danh mục).

## Routes (`routes/web.php` — THỨ TỰ QUAN TRỌNG)
```
GET /                          web.home             HomeController@index
GET /tat-ca-san-pham           web.products.index   ProductController@index
GET /tim-kiem                  web.search.index     SearchController@index      (TRƯỚC {slug})
GET /tim-kiem/goi-y            web.search.suggest   SearchController@suggest    (TRƯỚC {slug})
GET /danh-muc/{slug}           web.category.show    CategoryController@show     where [a-z0-9\-]+
GET /gio-hang                  web.cart.index       CartController@index
POST /gio-hang/them            web.cart.add         CartController@add
POST /gio-hang/cap-nhat/{itemId} web.cart.update    CartController@update
DELETE /gio-hang/xoa/{itemId}  web.cart.remove      CartController@remove
GET /gio-hang/count            web.cart.count       CartController@count
POST /gio-hang/ma-giam-gia/ap-dung  web.cart.coupon.apply
DELETE /gio-hang/ma-giam-gia       web.cart.coupon.remove
GET /thanh-toan                web.checkout.index   CheckoutController@index
POST /thanh-toan               web.checkout.store   CheckoutController@store
GET /dat-hang-thanh-cong/{order_number} web.checkout.success
GET /san-pham/{slug}           web.product.show     ProductController@show
GET /{slug}                    web.category.pretty  CategoryController@show     ← BẮT BUỘC CUỐI CÙNG
```

## Views (`resources/views`)
Layout `<x-layouts.app>` (components/layouts/app.blade.php): meta csrf-token, style.css, app.js + cart.js.
Pages: web/home, web/category, web/product, web/products, web/search, web/cart, web/checkout, web/checkout-success (+ home.blade.php cũ, welcome.blade.php mặc định).
Components Blade (`<x-...>`): header, footer, drawer, floatnav; sections/{hero, usp, collections, flash-sale, best-sellers, coupons, herbal-tea, testimonials, tips, trust, region, stats, newsletter}; product/{gallery, info, buybar, tabs, related, review-item, schema}; category/{chips, filters, filter-groups, filter-drawer, toolbar, pagination, seo-text}; ui/{breadcrumb, category-card, product-card, coupon-card, pagination}. Component tự ẩn khi data rỗng — không render "không có dữ liệu".

## CSS (`public/assets/css`)
`style.css` @import đúng thứ tự cascade 12 partials (01-base … 12-layout-extras); font import bên trong 01-base.css. Mobile-first, breakpoint max-width. Không đảo thứ tự @import, sửa style phải mở đúng partial.

## JS (`public/assets/js`)
- `app.js`: drawer qua `[data-drawer-open]`/`[data-drawer-close]` (class `is-open`), reveal `is-in`, spotlight `is-spotlight`, `show`, đếm `data-count`.
- `cart.js`: nút `.add-cart` POST fetch `/gio-hang/them` (header X-CSRF-TOKEN từ `meta[name="csrf-token"]`), đọc `input[name="variant_id"]:checked` hoặc `input[name="variant"]:checked` + `input[name="qty"]`, cập nhật `#cartBadge`, refresh `fetch('/gio-hang/count')`, toast `#toast`. Khi set input.value bằng JS phải `dispatchEvent(new Event('change'))`.

## Config & Helpers
`config/thaomoc.php`: order.prefix TMX (format TMX-YYYYMMDD-xxxxx, random 5 số); rate_limits(login 5, checkout 3, apply_coupon 10, search 30, send_otp 3)/phút; cache TTL theo nhóm (home: featured_categories 600, flash_sale 300, best_sellers 600, banners 3600; catalog: category_tree 3600, product_detail 300; content: posts 1800, testimonials 3600; settings 3600); shipping: default_fee 30000, **free_threshold env SHIPPING_FREE_THRESHOLD default 300000**, carrier nội bộ "Giao hàng nội bộ Thảo Mộc Farm"; upload 2MB jpeg/png/webp, thumbnails 300/600/1200; review require_verified_purchase true, auto_approve false.
Helpers (`app/Support/helpers.php`): `group_version_key(group)`, `remember_group(group,key,ttl,closure)` (key = `{group}:v{version}:{key}`, thay Cache::tags vì driver file), `bump_group_version(group): int`, `format_vnd(800000)→"800.000₫"`, `format_number_compact(3100)→"3.1k"`.

## Bẫy đã biết (không được phá)
1. Route catch-all `/{slug}` phải ở CUỐI routes/web.php.
2. Tên route web đều prefix `web.` (route('web.product.show', ...) v.v.).
3. Không có FK constraint → tự đảm bảo toàn vẹn dữ liệu trong Service.
4. `coupon_products`/`coupon_categories` không có timestamps.
5. Driver cache file → không dùng Cache::tags, chỉ remember_group/bump_group_version.
6. Selector JS là contract: #cartBadge, .add-cart, #toast, meta[name=csrf-token], data-drawer-* — đổi tên phải đổi đồng bộ Blade.
7. Tiền luôn là int VND; chỉ format ở tầng view bằng format_vnd().