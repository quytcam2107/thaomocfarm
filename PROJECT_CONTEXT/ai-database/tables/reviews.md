# Bảng `reviews`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T10:53:35+00:00

## Thông tin chung

- Số bản ghi: **2**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/reviews.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | YES | *NULL* | - | - |
| `ip_address` | varchar(45) | YES | *NULL* | - | - |
| `customer_name` | varchar(100) | YES | *NULL* | - | - |
| `customer_phone` | varchar(20) | YES | *NULL* | - | - |
| `order_id` | bigint unsigned | YES | *NULL* | - | - |
| `rating` | tinyint unsigned | NO | *NULL* | - | - |
| `content` | text | NO | *NULL* | - | - |
| `images` | json | YES | *NULL* | - | - |
| `is_verified` | tinyint(1) | NO | 0 | - | - |
| `admin_reply` | text | YES | *NULL* | - | - |
| `status` | enum('pending','approved','hidden') | NO | pending | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| reviews_order_id_index |  | btree | `order_id` |
| reviews_order_id_product_id_unique | ✔ | btree | `order_id`, `product_id` |
| reviews_product_id_index |  | btree | `product_id` |
| reviews_product_id_rating_index |  | btree | `product_id`, `rating` |
| reviews_product_id_status_created_at_index |  | btree | `product_id`, `status`, `created_at` |
| reviews_user_id_index |  | btree | `user_id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 2 — giá trị phổ biến: `1` (1), `2` (1)
- `product_id`: min = 1, max = 1 — giá trị phổ biến: `1` (2)
- `user_id`: min = 1, max = 1 — giá trị phổ biến: `NULL` (1), `1` (1)
- `ip_address`: giá trị phổ biến: `127.0.0.1` (1), `::1` (1)
- `customer_name`: giá trị phổ biến: `NULL` (1), `Quyết Lưu` (1)
- `customer_phone`: giá trị phổ biến: `NULL` (1), `0352806324` (1)
- `rating`: min = 5, max = 5 — giá trị phổ biến: `5` (2)
- `content`: giá trị phổ biến: `Hương vị rất thơm ngon, đóng gói cẩn thận. Tôi sẽ mua lại lần nữa.` (1), `Rất tốt, sản phẩm chuẩn Tây Bắc` (1)
- `images`: giá trị phổ biến: `NULL` (1), `["assets/images/reviews/rv_20261006_y1btee4rlzex2gze.png"]` (1)
- `is_verified`: min = 1, max = 1 — giá trị phổ biến: `1` (2)
- `status`: giá trị phổ biến: `approved` (2)
- `created_at`: min = 2026-10-05 19:12:11, max = 2026-10-06 07:27:50 — giá trị phổ biến: `2026-10-05 19:12:11` (1), `2026-10-06 07:27:50` (1)
- `updated_at`: min = 2026-10-05 19:12:11, max = 2026-10-06 07:27:50 — giá trị phổ biến: `2026-10-05 19:12:11` (1), `2026-10-06 07:27:50` (1)

## Mẫu dữ liệu

| id | product_id | user_id | ip_address | customer_name | customer_phone | order_id | rating | content | images | is_verified | admin_reply | status | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | 127.0.0.1 | NULL | NULL | NULL | 5 | Hương vị rất thơm ngon, đóng gói cẩn thận. Tôi sẽ mua lại lần nữa. | NULL | 1 | NULL | approved | 2026-10-05 19:12:11 | 2026-10-05 19:12:11
| 2 | 1 | NULL | ::1 | Quyết Lưu | 0352806324 | NULL | 5 | Rất tốt, sản phẩm chuẩn Tây Bắc | ["assets/images/reviews/rv_20261006_y1btee4rlzex2gze.png"] | 1 | NULL | approved | 2026-10-06 07:27:50 | 2026-10-06 07:27:50
