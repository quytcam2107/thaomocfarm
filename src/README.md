# Mộc Xanh — `src/` (Laravel application)

Thư mục này chứa **toàn bộ source code Laravel** của website thương mại điện tử thảo mộc & đặc sản Tây Bắc **Mộc Xanh**. Đây là app khách (web storefront) mobile-first; khu vực `/admin` chưa dựng.

> README gốc của repo (kiến trúc tổng thể, docker, quy trình AI) nằm ở `../README.md`.
> **Trước khi sửa code, BẮT BUỘC đọc** `../PROJECT_CONTEXT/README.md` (rule làm việc), `01-DATABASE.md`, `02-CODE.md`.

**Framework:** Laravel `^13.17` · **PHP:** `^8.3` · **Database:** MySQL 8 / MariaDB (`utf8mb4_unicode_ci`, InnoDB) · **Cache:** driver `file` · **Session:** `database` · **Prefix đơn hàng:** `TMX`

---

## 1. Chạy dự án (dev)

```bash
cd src
cp .env.example .env            # khai báo DB_*, SHIPPING_*, ORDER_PREFIX...
composer install
php artisan key:generate
php artisan migrate             # migrations 0001_01_01_* + 2026_01_01_* + các migration bổ sung
php artisan admin:import-locations   # nạp 34 tỉnh + 3.321 xã/phường vào provinces/wards
php artisan serve               # hoặc Nginx + PHP-FPM / docker-compose (xem ../docker)
```

Dữ liệu demo (sản phẩm, danh mục, coupon, flash sale, bài viết...) hiện KHÔNG có seeder đầy đủ — `DatabaseSeeder` chỉ tạo 1 user test. Dữ liệu thật được import tay; xem bản xuất trong `../PROJECT_CONTEXT/ai-database/`.

## 2. Cấu trúc thư mục `src/`

```text
src/
├── app/
│   ├── Console/Commands/     AiExportDatabase, ImportVietnamLocations
│   ├── DTOs/                 ProductViewDTO, CategoryViewDTO, RelatedProductDTO, ReviewViewDTO
│   ├── Enums/                11 backed string enums (OrderStatus, PaymentMethod, ...)
│   ├── Http/Controllers/Web/ 9 controller (Home, Product, Category, Search, Cart, Checkout, Blog, Page, Review)
│   ├── Jobs/                 (rỗng — chưa dispatch job nào)
│   ├── Models/               31 model Eloquent
│   ├── Providers/            AppServiceProvider (stub rỗng)
│   ├── Requests/             6 FormRequest (namespace App\Requests)
│   ├── Services/             Cart, Catalog, Checkout, Coupon, Home, Blog, Menu, Review, VietnamLocation
│   │   ├── Cart/CartTotals              (pure function tính tiền)
│   │   ├── Catalog/*                    (5 sub-service stateless)
│   │   ├── Coupon/CouponTexts           (sinh text coupon)
│   │   ├── Promotion/FlashSalePriceService  (NGUỒN GIÁ DEAL DUY NHẤT)
│   │   └── Review/ReviewPhoneMatcher    (đối chiếu SĐT verified-purchase)
│   ├── Support/helpers.php   remember_group, bump_group_version, format_vnd, ...
│   └── View/Components/      (rỗng — component Blade nằm trong resources/views)
├── bootstrap/, config/ (thaomoc, home, catalog, coupons), routes/ (web.php, console.php)
├── data/                     catalog-seo.json, vietnam_2_levels_v2.json (~940KB)
├── database/                 migrations (38 file), factories (chỉ UserFactory), seeders
├── public/assets/            css/partials (22 file), js modules, images, vendor (lightgallery, rateyo, jquery)
├── resources/views/          components/* + web/* (Blade)
└── tests/
```

## 3. Artisan commands của app

| Lệnh | Tác dụng |
|---|---|
| `php artisan ai:export-database` | Xuất cấu trúc + TOÀN BỘ dữ liệu mọi bảng ra `../PROJECT_CONTEXT/ai-database/` (`INDEX.md`, `tables/*.md`, `data/*.jsonl`). Chỉ SELECT. Options: `--connection=`, `--output=`, `--include-framework`. |
| `php artisan admin:import-locations` | Import `data/vietnam_2_levels_v2.json` → bảng `provinces` + `wards` (upsert theo `code`, gắn FK thật `wards.province_code → provinces.code`). Options: `--path=`, `--fresh`, `--dry-run`. |

## 4. Các phân hệ nghiệp vụ (đang chạy trên web khách)

| Phân hệ | Route chính | Controller → Service |
|---|---|---|
| Trang chủ | `GET /` | `HomeController` → `HomeService`, `BlogService::homeTips()` |
| Catalog | `/danh-muc/{slug}`, `/tat-ca-san-pham`, `/san-pham/{slug}`, `/tim-kiem`, `/tim-kiem/goi-y`, alias `/{slug}` | `CategoryController`, `ProductController`, `SearchController` → `CatalogService` + `Catalog/*`, `Promotion/FlashSalePriceService` |
| Giỏ hàng + coupon | `/gio-hang/*` | `CartController` → `CartService` (+`Cart/CartTotals`), `CouponService` (+`Coupon/CouponTexts`) |
| Checkout COD + trang thành công | `/thanh-toan`, `/dat-hang-thanh-cong/{order_number}` | `CheckoutController` → `CheckoutService` |
| **Đánh giá sản phẩm (review)** | `POST /san-pham/{slug}/danh-gia`, `POST /danh-gia/{review}/huu-ich`, `POST /danh-gia/{review}/phan-hoi` | `ReviewController` → `ReviewService` (+`Review/ReviewPhoneMatcher`) |
| Cẩm nang (blog) | `/cam-nang`, `/cam-nang/category/{slug}`, `/cam-nang/{slug}` | `BlogController` → `BlogService` |
| Trang tĩnh | `/gioi-thieu`, `/lien-he`, `/huong-dan-dat-hang`, `/chinh-sach-doi-tra`, `/chinh-sach-bao-mat`, `/dieu-khoan-su-dung` | `PageController` |
| Menu drawer categories | (component `x-drawer`) | `MenuService` |

## 5. Quy ước cốt lõi (tắt — chi tiết trong `../PROJECT_CONTEXT/`)

- **Route**: tên luôn prefix `web.`; catch-all `GET /{slug}` (`web.category.alias`) BẮT BUỘC cuối `routes/web.php`, mọi route mới đặt TRƯỚC nó.
- **Database**: FK lưu `unsignedBigInteger`, KHÔNG ràng buộc foreign key (trừ `wards→provinces` gắn lúc import) — toàn vẹn do Service đảm bảo. Tiền `int unsigned` VND, chỉ format ở view bằng `format_vnd()`. Enum MySQL khớp PHP Enum trong `app/Enums`.
- **Cache**: driver `file` → CẤM `Cache::tags()`. Dùng `remember_group($group,$key,$ttl,$fn)` / `bump_group_version($group)`. Nhóm: `home`, `catalog`, `content`, `review`, `settings`.
- **Giá flash**: `FlashSalePriceService` là nguồn sự thật duy nhất. `flash_price` = giá GỐC gạch ngang; GIÁ BÁN = `round(flash_price × (100−discount_percent)/100)`. Sửa deal → `flush()`.
- **Checkout**: ghi `stock_movements.type='export'` (qty ÂM), trừ tồn bằng conditional UPDATE nguyên tử; KHÔNG tạo `payments`, KHÔNG ghi `order_status_histories`. `order_number` = `TMX-YYYYMMDD-XXXXX` (XXXXX = 5 ký tự alphanumeric IN HOA).
- **Frontend**: CSS chia partials `@import` trong `style.css` theo cascade (không đảo, không viết rule trực tiếp). JS ES-module tĩnh, khai `<script type="importmap">` key `@tm/*`; module mới PHẢI khai thêm importmap. Component Blade tự ẩn khi data rỗng.

## 6. Trạng thái & việc chưa làm

- ✅ Web khách: home, catalog, search, PDP (flash sale, gallery lightgallery, tabs, **review + rateyo + helpful + admin reply**), cart + coupon, checkout COD, cảm ơn, blog, trang tĩnh.
- ✅ Dữ liệu hành chính 2 cấp (`provinces`/`wards`) đã import — nhưng **checkout vẫn dùng `<select>` tỉnh/quận hardcode**, chưa có cascading select JS/route nối vào `wards`.
- ⛔ Chưa có khu vực `/admin` (schema + role `admin/staff` đã sẵn sàng).
- ⛔ Auth login/register chưa lên UI (`User`, `mergeGuestCart` đã có cho luồng login tương lai).
- ⛔ Thanh toán online: `PaymentMethod` chỉ `cod` (`bank_transfer` đang comment trong PHP, DB enum vẫn chứa); `payments` chưa được ghi.
- 🐛 Known issues:
  - `#[UseFactory(...)]` trỏ factory CHƯA tồn tại trên **26/27 model** (chỉ `UserFactory` có thật) → `Model::factory()` (khác User) sẽ fatal.
  - `ai:export-database` sinh `INDEX.md` **lặp mỗi bảng ~3 dòng** (trừ `provinces`/`wards` 1 dòng) → "94 bảng" thực chất là **33 bảng**; tin `tables/*.md`.
  - Hai migration trùng prefix timestamp `2026_10_07_000001` (`add_specs_json_to_products` và `create_wards`).
  - `Order.php` khai báo casts 2 lần (property `$casts` + method `casts()`).

## 7. Kiểm tra nhanh trước khi commit

- `php artisan route:list` — xác nhận catch-all `/{slug}` vẫn cuối, route mới prefix `web.`.
- Review/JS: importmap `@tm/*` khớp `import` trong module; `window.updateCartCount(n)`, `.add-cart`, `#toast`, `meta[name="csrf-token"]` giữ nguyên contract.
- Đổi schema/data → chạy lại `php artisan ai:export-database` và cập nhật tay `../PROJECT_CONTEXT/01-DATABASE.md` / `02-CODE.md`.
