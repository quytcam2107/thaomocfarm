# PROJECT_CONTEXT — Tài liệu ngữ cảnh dự án Mộc Xanh

Mục đích: cung cấp cho AI trước khi sửa code, để bám sát code & database hiện có, không bịa tên.

| File | Nội dung |
|---|---|
| `README.md` (file này) | Rule làm việc bắt buộc + quy ước kỹ thuật cốt lõi |
| `01-DATABASE.md` | Tổng quan **30 bảng nghiệp vụ** (bản xuất hiện tại) + 8 bảng framework: quy ước cột/index/enum/unique, quan hệ giữa bảng, bẫy thiết kế — maintain tay |
| `02-CODE.md` | Models/scopes/relations, 11 Enums, 4 DTOs, 8 services chính + 8 sub-services, 8 Controllers, 5 FormRequests, routes/web.php theo thứ tự thật, views/components Blade, 15 CSS partials, kiến trúc JS module (importmap), contract JS, config (thaomoc/home/catalog/coupons), helpers, lệnh Artisan, bẫy đã biết |
| `ai-database/INDEX.md` *(auto)* | Bản xuất database: danh sách bảng + số bản ghi + link file chi tiết — do `php artisan ai:export-database` sinh |
| `ai-database/tables/<bảng>.md` *(auto)* | Cấu trúc THẬT từng bảng (cột, kiểu, null, default, index, FK, comment) + thống kê min/max/top values + 50 dòng mẫu |
| `ai-database/data/<bảng>.jsonl` *(auto)* | TOÀN BỘ dữ liệu từng bảng, mỗi dòng = 1 bản ghi JSON |

*(auto) = sinh tự động, KHÔNG sửa tay — chạy lại `php artisan ai:export-database` (trong `src/`) bất cứ khi nào cần cập nhật; lệnh chỉ đọc (SELECT).*

> ⚠️Lỗi bản xuất hiện tại: `INDEX.md` liệt kê MỖI BẢNG HAI LẦN (tổng "60 bảng" thực chất là 30 bảng ×2). Số bản ghi mỗi bảng trong cột "Số bản ghi" vẫn đúng. Không sửa tay file auto — khi cần đối chiếu hãy đếm trực tiếp `tables/*.md`.

## Cách dùng cho AI
1. Đọc `README.md` này (rule làm việc) + `02-CODE.md`.
2. Về database: đọc `ai-database/INDEX.md` (lưu ý lỗi trùng dòng trên); đọc `ai-database/tables/<bảng>.md` cho cấu trúc + mẫu; chỉ mở `ai-database/data/<bảng>.jsonl` khi cần dữ liệu đầy đủ.
3. Khi tài liệu và code/database thực tế khác nhau → **thực tế thắng**, báo lại chênh lệch.

---

## RULE LÀM VIỆC — ÁP DỤNG CHO MỌI YÊU CẦU

### Bối cảnh dự án
- Dự án Laravel 13 (Blade + CSS/JS tĩnh trong `public/assets`), mobile-first, source code nằm trong `src/`.
- Bản code chuẩn để đối chiếu là nhánh `main` của git.
- Tài liệu này phản ánh ĐÚNG code thực tế tại `src/`; dữ liệu database luôn lấy từ bản xuất mới nhất (`ai-database/`).

### 1. Đọc trước khi làm
- TRƯỚC khi sửa bất cứ gì, đọc kỹ kiến trúc và TOÀN BỘ file liên quan: layout, component Blade, routes, controller, service, CSS partials (`public/assets/css/partials/*`), JS assets (`public/assets/js/*`).
- Tên cột/kiểu/dữ liệu THẬT lấy từ `ai-database/tables/<bảng>.md` và `ai-database/data/<bảng>.jsonl` — không đoán.
- Đối chiếu 01-DATABASE.md / 02-CODE.md + bản xuất trước; lệch → thực tế thắng + báo cáo.

### 2. Bám sát code & database hiện có
- Viết theo ĐÚNG code/database đang có: tên route, bảng/cột, class, component, selector... KHÔNG bịa, KHÔNG giả định.
- Giữ tương thích ngược các contract JS: `#cartBadge` (selector thực tế rộng hơn: `.js-cart-count, .cart-count` — xem 02-CODE.md §11), `.add-cart`, `#toast`, `meta[name="csrf-token"]`, `[data-drawer-open]`/`[data-drawer-close]`, `window.updateCartCount(n)`, importmap keys `@tm/*`.
- Không đụng phần đang chạy ổn; không refactor/đổi tên/thêm tính năng ngoài yêu cầu.

### 3. Cách xuất code (BẮT BUỘC)
- KHÔNG diff, KHÔNG "thêm đoạn này vào chỗ kia".
- File cần sửa/tạo → xuất TOÀN BỘ NỘI DUNG (dòng 1 → dòng cuối) trong MỘT code block duy nhất, trên đầu ghi `### File: đường/dẫn/file` (đường dẫn tương đối từ `src/`).
- Phần không thuộc phạm vi chỉnh sửa giữ NGUYÊN VĂN bản gốc (comment, format, thứ tự).
- Chỉ liệt kê file thực sự thay đổi.

### 4. Chất lượng giải pháp
- Code hoàn chỉnh, không placeholder "...".
- Comment tiếng Việt ngắn gọn tại logic mới.
- Giải pháp đa file phải liệt kê ĐỦ mắt xích: CSS ↔ Blade ↔ JS ↔ Route/Controller/Service.

### 5. Kết thúc câu trả lời
- Tóm tắt: đã sửa file nào, thay đổi gì, luồng hoạt động sau sửa.
- Nêu rõ điểm cần người dùng kiểm tra lại trên trình duyệt/database local trước khi commit.

---

## QUY ƯỚC KỸ THUẬT CỐT LÕI (tắt — chi tiết: 02-CODE.md)

### Routing
- Tên route web luôn prefix `web.`: `route('web.product.show', $product->slug)`.
- Catch-all `GET /{slug}` (`web.category.pretty`) BẮT BUỘC CUỐI cùng `routes/web.php`; mọi route mới đặt TRƯỚC nó (nhóm `/tim-kiem*`, `/gio-hang/*`, `/thanh-toan`, `/san-pham/{slug}`, `/huong-dan-dat-hang`, `/chinh-sach-*`, `/dieu-khoan-su-dung`, `/gioi-thieu`, `/lien-he`, `/cam-nang*` đều đã nằm trước).
- URL thân thiện: `/san-pham/{slug}`, `/danh-muc/{slug}`, `/tim-kiem`, `/gio-hang`, `/thanh-toan`, `/cam-nang`.

### Database
- FK lưu `unsignedBigInteger`, KHÔNG ràng buộc foreign key — toàn vẹn dữ liệu do Service đảm bảo.
- Tiền tệ: `int unsigned` VND; chỉ format ở view bằng `format_vnd()`.
- Enum MySQL backed string khớp PHP Enum trong `app/Enums` (11 enum).
- `coupon_products`/`coupon_categories` KHÔNG có timestamps.
- FULLTEXT `search_name(name)` trên products tạo qua `DB::statement` sau `Schema::create` (SQLite dev phải bỏ qua).
- `orders.order_number` format thật: `TMX-YYYYMMDD-XXXXX` với XXXXX = **5 ký tự chữ/số in HOA** (`strtoupper(Str::random(5))`, vd `TMX-20260925-G7RPB`).

### Services & Flash Sale (quan trọng — dễ sai)
- `FlashSalePriceService` là nguồn sự thật DUY NHẤT cho giá deal: `flash_price` = GIÁ GỐC gạch ngang, `discount_percent` áp TRÊN flash_price, GIÁ BÁN = round(flash_price × (100−discount_percent)/100). Cache nhóm `catalog`, sửa deal phải `flush()`.
- Checkout ghi `stock_movements.type='export'` (qty ÂM), trừ tồn kho variant bằng conditional UPDATE nguyên tử; KHÔNG ghi `order_reserve`, KHÔNG tạo `payments`/`order_status_histories` tại thời điểm đặt.

### Cache & Helpers
- Driver cache = `file` → CẤM `Cache::tags()`. Dùng `remember_group($group,$key,$ttl,$fn)` / `bump_group_version($group)` (`app/Support/helpers.php`).
- Nhóm cache thực dùng: `home`, `catalog`, `content`, `settings` (+ `review` theo config TTL). Chưa có artisan bump — muốn bump gọi helper qua tinker.
- Artisan command duy nhất của app: `ai:export-database`.

### Frontend
- Mobile-first; CSS chia **15 partials** `public/assets/css/partials/01-base … 15-blog`; `style.css` @import theo cascade — KHÔNG đảo, KHÔNG viết rule trực tiếp vào style.css, font import nằm TRONG 01-base.css.
- Component Blade tự ẩn khi data rỗng — không render "không có dữ liệu".
- JS tĩnh ES-module: layout khai `<script type="importmap">` key `@tm/core|ui|backtotop|product|search|cart-badge|cart-add`; 2 loader `assets/js/app.js` + `assets/js/cart.js` dynamic-import theo điều kiện DOM. Module mới PHẢI khai thêm importmap. Set `input.value` bằng JS phải `dispatchEvent(new Event('change'))`.

### Bảo mật & Config
- CSRF: form Blade `@csrf`; fetch POST gắn `X-CSRF-TOKEN` từ `meta[name="csrf-token"]`.
- Rate limit THỰC TẾ: gọi `RateLimiter::tooManyAttempts/hit/clear` trực tiếp trong controller (không middleware throttle): `apply_coupon` 10/phút (CartController), `search` suggest 30/phút (SearchController). Các key `login/checkout/send_otp` trong `config/thaomoc.php` chưa được dùng (chuẩn bị cho auth).
- Shipping: `standard` free khi ≥ `SHIPPING_FREE_THRESHOLD` (default 300000), ngược lại `default_fee` 30000; `fast` luôn thu `fast_fee` 30000 (không áp ngưỡng freeship).

---

## PROMPT MẪU
> "Đây là dự án Laravel 13 mobile-first (source trong `src/`). Tài liệu ngữ cảnh đính kèm: [README.md + 01-DATABASE.md + 02-CODE.md]; dữ liệu database thật trong `PROJECT_CONTEXT/ai-database/` (INDEX.md, tables/*.md, data/*.jsonl). Tuân thủ tuyệt đối RULE LÀM VIỆC và tên route/cột/class/selector thật. Chỉ xuất toàn bộ nội dung file thay đổi, mỗi file một code block."

## CẬP NHẬT TÀI LIỆU
- **Database**: chạy `php artisan ai:export-database` trong `src/` → tự ghi đè `PROJECT_CONTEXT/ai-database/INDEX.md`, `tables/`, `data/` (output mặc định: thư mục `PROJECT_CONTEXT/ai-database` CÙNG CẤP với `src/`; nếu repo không nằm trong `src` thì fallback `base_path()/PROJECT_CONTEXT/ai-database`).
- **Code**: khi thêm migration/model/route/CSS partial/component/JS module/config mới → cập nhật tay 01-DATABASE.md / 02-CODE.md cho khớp (module JS mới khai thêm ở importmap layout + bảng §11), commit kèm.