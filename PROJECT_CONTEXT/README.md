# PROJECT_CONTEXT — Tài liệu ngữ cảnh dự án Thảo Mộc Farm

Mục đích: dán kèm vào prompt khi yêu cầu AI sửa/tính năng mới, để AI bám sát code & database hiện có, không bịa tên.

| File | Nội dung |
|---|---|
| `01-DATABASE.md` | Tổng quan 34 bảng nghiệp vụ + 7 bảng framework: quy ước, quan hệ giữa bảng, bẫy thiết kế (maintain tay) |
| `02-CODE.md` | Models/scopes/relations, 11 Enums, 4 DTOs, 5 Services + method công khai, Controllers, 5 FormRequests (rules thật), routes/web.php theo thứ tự, views/components Blade, cấu trúc CSS partials, kiến trúc JS module (app.js/cart.js loader + importmap + các module ES), contract JS, config/thaomoc.php, helpers, lệnh Artisan (`ai:export-database`), danh sách "bẫy đã biết" |
| `INDEX.md` *(auto)* | Bản xuất database: danh sách toàn bộ bảng + số bản ghi + link tới file chi tiết — do `php artisan ai:export-database` sinh |
| `tables/<bảng>.md` *(auto)* | Cấu trúc thật từng bảng (cột, kiểu, null, default, index, FK, comment) + thống kê min/max/top values + 50 dòng mẫu dữ liệu |
| `data/<bảng>.jsonl` *(auto)* | TOÀN BỘ dữ liệu từng bảng, mỗi dòng = 1 bản ghi JSON (không giới hạn record) |

*(auto) = sinh tự động, KHÔNG sửa tay — chạy lại `php artisan ai:export-database` bất cứ khi nào cần cập nhật; lệnh chỉ đọc (SELECT), không ảnh hưởng dữ liệu.*

## Cách dùng cho AI
1. Đọc `README.md` này (rule làm việc) + `02-CODE.md`.
2. Về database: đọc `INDEX.md` để biết có bảng nào / bao nhiêu bản ghi; đọc `tables/<bảng>.md` cho cấu trúc + mẫu; chỉ mở `data/<bảng>.jsonl` khi cần dữ liệu đầy đủ (file có thể lớn).
3. Khi tài liệu và code thực tế khác nhau → code thực tế thắng, báo lại chênh lệch.

---

## RULE LÀM VIỆC — ÁP DỤNG CHO MỌI YÊU CẦU

### Bối cảnh dự án
- Dự án Laravel (Blade + CSS/JS tĩnh trong public/assets), giao diện mobile-first, source code nằm trong `src/`.
- Bản code chuẩn để đối chiếu là nhánh `main` của git.
- Tài liệu này (01-DATABASE.md + 02-CODE.md) phản ánh ĐÚNG code thực tế tại `src/`; dữ liệu database luôn lấy từ bản xuất mới nhất (`INDEX.md`, `tables/`, `data/`).

### 1. Đọc trước khi làm
- TRƯỚC khi sửa bất cứ gì, phải đọc kỹ kiến trúc và nội dung TOÀN BỘ file liên quan: layout, component Blade, routes, controller, các CSS partials (`public/assets/css/partials/*`), JS assets (`public/assets/js/*`).
- Hiểu rõ naming convention, cấu trúc class CSS, tên route, tên cột database đang dùng thực tế — tên cột/kiểu liệu THẬT lấy từ `tables/<bảng>.md`, dữ liệu thật lấy từ `data/<bảng>.jsonl` (không đoán từ file này).
- Đối chiếu với 01-DATABASE.md / 02-CODE.md + bản xuất database trước; nếu tài liệu và code thực tế khác nhau → code thực tế thắng, và phải báo lại chênh lệch.

### 2. Bám sát code & database hiện có
- Luôn viết theo ĐÚNG code và database đang có: tên route, tên bảng/cột, class, component, biến... lấy từ thực tế dự án, KHÔNG bịa đặt, KHÔNG giả định.
- Giữ tương thích ngược: nếu nơi khác đang tham chiếu selector/id/class nào thì không được phá vỡ nó (đặc biệt các contract JS: `#cartBadge`, `.add-cart`, `#toast`, `meta[name="csrf-token"]`, `[data-drawer-open]`/`[data-drawer-close]`, `window.updateCartCount`).
- Không làm ảnh hưởng các phần đang chạy ổn: chỉ đụng đúng phạm vi chức năng được yêu cầu. Không refactor, không đổi tên, không thêm tính năng ngoài yêu cầu.

### 3. Cách xuất code (BẮT BUỘC)
- KHÔNG hiển thị diff, KHÔNG xuất từng dòng sửa, KHÔNG mô tả "thêm đoạn này vào chỗ kia".
- File nào cần sửa/tạo mới → xuất TOÀN BỘ NỘI DUNG FILE ĐẦY ĐỦ (từ dòng 1 đến dòng cuối) trong MỘT code block duy nhất, để tôi copy nguyên và ghi đè file là chạy được ngay.
- Trước mỗi code block phải ghi rõ đường dẫn file, ví dụ:
  ### File: resources/views/components/floatnav.blade.php
- Nội dung không thuộc phần chỉnh sửa phải giữ NGUYÊN VĂN bản gốc (kể cả comment, format, thứ tự).
- Chỉ liệt kê các file thực sự thay đổi; không xuất lại file không sửa.

### 4. Chất lượng giải pháp
- Code phải hoàn chỉnh, không có placeholder kiểu "...", "// giữ nguyên phần còn lại".
- Có comment ngắn gọn bằng tiếng Việt tại các điểm logic mới/thay đổi để dễ bảo trì.
- Nếu giải pháp cần thay đổi nhiều file, liệt kê đầy đủ TẤT CẢ file đó, không bỏ sót mắt xích (CSS ↔ Blade ↔ JS ↔ Route/Controller).

### 5. Kết thúc câu trả lời
- Tóm tắt ngắn: đã sửa file nào, mỗi file thay đổi điều gì, luồng hoạt động sau khi sửa.
- Nêu rõ điểm nào cần tôi kiểm tra lại trên trình duyệt/database (nếu có).

---

## QUY ƯỚC KỸ THUẬT CỐT LÕI (tắt, chi tiết xem 02-CODE.md)

### Routing
- Tên route web luôn prefix `web.`: `route('web.product.show', $product->slug)`.
- Route catch-all `GET /{slug}` (web.category.pretty) BẮT BUỘC nằm CUỐI cùng `routes/web.php`; mọi route mới phải đặt trước nó.
- URL thân thiện: `/san-pham/{slug}`, `/danh-muc/{slug}`, `/tim-kiem`, `/gio-hang`, `/thanh-toan`.

### Database
- FK lưu `unsignedBigInteger`, KHÔNG ràng buộc foreign key — toàn vẹn dữ liệu do Service đảm bảo.
- Tiền tệ: `unsignedInteger` đơn vị VND, chỉ format ở view bằng `format_vnd()`.
- Enum MySQL backed string khớp enum PHP trong `app/Enums` (OrderStatus, CouponType...).
- `coupon_products`/`coupon_categories` KHÔNG có timestamps.
- FULLTEXT `search_name(name)` trên bảng products tạo qua `DB::statement`, không khai trong Schema::create.

### Cache & Helpers
- Driver cache = file → CẤM dùng `Cache::tags`. Dùng `remember_group($group, $key, $ttl, $fn)` và `bump_group_version($group)` (helpers trong `app/Support/helpers.php`).
- Nhóm cache thực tế: `home`, `catalog`, `content`, `settings`, `review`. Chưa có artisan command bump cache — muốn bump phải gọi helper qua tinker. Lệnh artisan duy nhất hiện có: `ai:export-database` (xem 02-CODE.md).

### Frontend
- Mobile-first; CSS chia 12 partials trong `public/assets/css/partials/`, `style.css` @import theo thứ tự cascade — KHÔNG đảo thứ tự, sửa style phải mở đúng partial.
- Component Blade tự ẩn khi data rỗng — không render thông báo "không có dữ liệu".
- JS tĩnh: `app.js` + `cart.js` là 2 loader `type="module"` mỏng; code thật tách thành các module ES trong `public/assets/js/` (`app-core`, `app-ui`, `app-backtotop`, `app-product`, `app-search`, `cart-badge`, `cart-add`) nạp qua `importmap` + dynamic import theo điều kiện DOM. Chi tiết mục 11 của 02-CODE.md. Set `input.value` bằng JS phải `dispatchEvent(new Event('change'))`.

### Bảo mật & Config
- CSRF: form Blade có `@csrf`; fetch POST gắn header `X-CSRF-TOKEN` đọc từ `meta[name="csrf-token"]`.
- Rate limit theo `config/thaomoc.php`: login 5/phút, checkout 3/phút, apply_coupon 10/phút, search 30/phút.
- Shipping free threshold: env `SHIPPING_FREE_THRESHOLD`, default code = **300000** (kiểm tra .env production nếu thấy khác).

---

## PROMPT MẪU
> "Đây là dự án Laravel mobile-first (source trong `src/`). Tài liệu ngữ cảnh đính kèm: [dán README.md + 01-DATABASE.md + 02-CODE.md]; dữ liệu database thật nằm trong `PROJECT_CONTEXT/INDEX.md`, `tables/*.md`, `data/*.jsonl`. Hãy tuân thủ tuyệt đối RULE LÀM VIỆC trong README.md và tên route/cột/class/selector trong tài liệu. Chỉ xuất toàn bộ nội dung file thay đổi."

## CẬP NHẬT TÀI LIỆU
- **Database (cấu trúc + dữ liệu)**: chạy lại `php artisan ai:export-database` (trong thư mục `src/`) — tự ghi đè `INDEX.md`, `tables/`, `data/`; không cần làm gì thêm, chỉ đọc được cập nhật ngay.
- **Code**: khi thêm migration/model/route/CSS partial/component/JS module mới, cập nhật tay 01-DATABASE.md / 02-CODE.md cho khớp thực tế (module JS mới phải khai thêm trong importmap của layout + bảng ở mục 11), rồi commit kèm.