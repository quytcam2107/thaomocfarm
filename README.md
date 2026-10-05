# Mộc Xanh — README

Website thương mại điện tử **thảo mộc & đặc sản Tây Bắc**, Laravel 13 + Blade, giao diện mobile-first, CSS/JS tĩnh đặt trong `public/assets` (không build pipeline cho frontend chính). Toàn bộ source application nằm trong thư mục `src/`.

**Mã dự án:** MX · **Framework:** Laravel 13 (`laravel/framework ^13.17`) · **PHP:** `^8.3` · **Database:** MySQL 8 / MariaDB (`utf8mb4_unicode_ci`, InnoDB)

---

## 1. Kiến trúc vận hành

```text
Internet → Nginx → PHP-FPM → Laravel 13 (mono-app: web khách + admin tương lai)
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
        MySQL 8/MariaDB   File cache      Local public storage
        (+ FULLTEXT)      (driver file)   (public/assets/images)
```

- 1 VPS duy nhất; KHÔNG Redis / Meilisearch / Elasticsearch / Octane.
- Cache: driver `file` → cấm `Cache::tags()`, dùng helper version-group (xem PROJECT_CONTEXT/02-CODE.md §13).
- Session: `database` driver. Queue: `database` (các bảng jobs đã có nhưng UI web hiện chưa dispatch job).
- Search: MySQL FULLTEXT `search_name(name)` + LIKE fallback.

## 2. Cấu trúc thư mục gốc repo

```text
/                       ← repo này
├── README.md           ← file bạn đang đọc
├── Prompt_AI.txt       ← prompt mẫu gửi AI
├── docker/, Dockerfile, docker-compose.yml   ← môi trường dev container
├── PROJECT_CONTEXT/    ← TÀI LIỆU NGỮ CẢNH CHO AI (đọc trước khi sửa code)
│   ├── README.md       ← rule làm việc + quy ước cốt lõi
│   ├── 01-DATABASE.md  ← quy ước schema, quan hệ, bẫy thiết kế
│   ├── 02-CODE.md      ← models/services/controllers/routes/views/CSS/JS/config
│   └── ai-database/    ← BẢN XUẤT DATABASE TỰ ĐỘNG (INDEX.md, tables/*.md, data/*.jsonl)
└── src/                ← toàn bộ source Laravel app
    ├── app/            (Console, DTOs, Enums, Http/Controllers/Web, Models,
    │                   Providers, Requests, Services, Support/helpers.php)
    ├── bootstrap/, config/, database/ (migrations, factories, seeders),
    ├── data/           (catalog-seo.json — nội dung SEO đọc bởi CatalogService)
    ├── public/assets/  (css/partials 01→15, js modules, images, vendor/lightgallery)
    ├── resources/views/(components/*, web/*)
    └── routes/web.php
```

## 3. Các phân hệ nghiệp vụ chính (đã chạy trên web khách)

| Phân hệ | Route chính | Controller / Service |
|---|---|---|
| Trang chủ (hero, flash sale, best sellers, coupons, trà hoa, tips từ blog) | `GET /` | `HomeController` → `HomeService`, `BlogService::homeTips()` |
| Catalog: danh mục / tất cả SP / chi tiết SP / tìm kiếm + gợi ý | `/danh-muc/{slug}`, `/tat-ca-san-pham`, `/san-pham/{slug}`, `/tim-kiem`, `/tim-kiem/goi-y`, alias `/{slug}` | `CategoryController`, `ProductController`, `SearchController` → `CatalogService` + `Catalog/*`, `Promotion/FlashSalePriceService` |
| Giỏ hàng + coupon (thêm, mua nhanh, cập nhật, xoá, áp/gỡ mã, badge) | `/gio-hang/*` | `CartController` → `CartService` (+`Cart/CartTotals`), `CouponService` (+`Coupon/CouponTexts`) |
| Checkout COD + trang thành công | `/thanh-toan`, `/dat-hang-thanh-cong/{order_number}` | `CheckoutController` → `CheckoutService` |
| Cẩm nang (blog) | `/cam-nang`, `/cam-nang/category/{slug}`, `/cam-nang/{slug}` | `BlogController` → `BlogService` |
| Trang tĩnh (about/contact/order-guide/return/privacy/terms) | `/gioi-thieu`, `/lien-he`, `/huong-dan-dat-hang`, `/chinh-sach-doi-tra`, `/chinh-sach-bao-mat`, `/dieu-khoan-su-dung` | `PageController` |
| Menu drawer categories | (component x-drawer) | `MenuService` |

## 4. Chạy dự án (dev)

```bash
cd src
cp .env.example .env            # khai báo DB + SHIPPING_FREE_THRESHOLD...
composer install
php artisan key:generate
php artisan migrate --seed      # migrations đầy đủ 2026_01_01_* + framework tables
php artisan serve               # hoặc Nginx + PHP-FPM / docker-compose up
```

Cập nhật ngữ cảnh database cho AI (chỉ SELECT, chạy bất cứ khi nào schema/data đổi):

```bash
cd src && php artisan ai:export-database
# output: ../PROJECT_CONTEXT/ai-database/ (INDEX.md, tables/, data/)
```

## 5. Quy trình làm việc với AI

Trước mọi yêu cầu sửa code: đọc `PROJECT_CONTEXT/README.md` (rule bắt buộc), `01-DATABASE.md`, `02-CODE.md`, và `PROJECT_CONTEXT/ai-database/INDEX.md`. Khi tài liệu khác thực tế → **thực tế thắng**, AI phải báo lại chênh lệch. Chi tiết cách xuất code (toàn bộ file, một code block/file, không diff, không placeholder) xem `PROJECT_CONTEXT/README.md`.

## 6. Trạng thái hiện tại & việc chưa làm

- ✅ Web khách hoàn chỉnh: home, catalog, search, PDP (flash sale, gallery lightgallery, tabs, review slot), cart + coupon, checkout COD, cảm ơn, blog, trang tĩnh.
- ⛔ Chưa có khu vực `/admin` (routes/models đã sẵn sàng ở tầng DB; README kiến trúc cũ mô tả admin là định hướng).
- ⛔ Auth login/register chưa lên UI (bảng `users`, model `User` với role enum đã có; `mergeGuestCart` sẵn sàng cho login flow).
- ⛔ Thanh toán online: `PaymentMethod` chỉ COD, `bank_transfer` đang comment; bảng `payments` chưa được CheckoutService ghi.
- 🐛 Known issues: `Post.php` tham chiếu `PostFactory` chưa tồn tại trong `database/factories/`; `ai:export-database` sinh `INDEX.md` bị lặp mỗi bảng 2 dòng (không ảnh hưởng `tables/`, `data/`).

## 7. Bối cảnh tên gọi

Thương hiệu hiển thị: **Mộc Xanh**, prefix đơn hàng `MX` (config `thaomoc.order.prefix`, env `ORDER_PREFIX`).