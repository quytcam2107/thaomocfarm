# Bảng `coupons`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T08:42:58+00:00

## Thông tin chung

- Số bản ghi: **4**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/coupons.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `code` | varchar(255) | NO | *NULL* | - | - |
| `type` | enum('fixed','percent','shipping') | NO | *NULL* | - | - |
| `value` | int unsigned | NO | *NULL* | - | - |
| `min_order_value` | int unsigned | NO | 0 | - | - |
| `max_discount` | int unsigned | YES | *NULL* | - | - |
| `starts_at` | timestamp | YES | *NULL* | - | - |
| `expires_at` | timestamp | YES | *NULL* | - | - |
| `usage_limit` | int unsigned | YES | *NULL* | - | - |
| `per_user_limit` | smallint unsigned | NO | 1 | - | - |
| `status` | enum('active','inactive') | NO | active | - | - |
| `description` | text | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| coupons_code_unique | ✔ | btree | `code` |
| coupons_status_expires_at_index |  | btree | `status`, `expires_at` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `code`: giá trị phổ biến: `SALE22K` (1), `SALE9K` (1), `SALETO300K` (1), `SALETO500K` (1)
- `type`: giá trị phổ biến: `percent` (2), `fixed` (2)
- `value`: min = 8, max = 22000 — giá trị phổ biến: `10` (1), `9000` (1), `22000` (1), `8` (1)
- `min_order_value`: min = 99000, max = 500000 — giá trị phổ biến: `500000` (1), `99000` (1), `222000` (1), `300000` (1)
- `max_discount`: min = 30000, max = 69000 — giá trị phổ biến: `NULL` (2), `69000` (1), `30000` (1)
- `expires_at`: min = 2026-12-31 23:59:59, max = 2026-12-31 23:59:59 — giá trị phổ biến: `2026-12-31 23:59:59` (4)
- `per_user_limit`: min = 1, max = 1 — giá trị phổ biến: `1` (4)
- `status`: giá trị phổ biến: `active` (4)
- `description`: giá trị phổ biến: `Giảm 10% cho đơn hàng giá trị tối thiểu 500K. Mã giảm tối đa 69K.` (1), `Mã giảm 9K cho đơn hàng tối thiểu 99K. Tối đa 1 mã giảm giá/đơn hàng.` (1), `Mã giảm 22k cho đơn hàng tối thiểu 222K. Tối đa 1 mã giảm giá/đơn hàng.` (1), `Giảm 8% cho đơn hàng giá trị tối thiểu 300K. Mã giảm tối đa 30K.` (1)
- `created_at`: min = 2026-09-26 15:31:21, max = 2026-09-26 15:31:21 — giá trị phổ biến: `2026-09-26 15:31:21` (4)
- `updated_at`: min = 2026-09-26 15:31:21, max = 2026-09-26 15:31:21 — giá trị phổ biến: `2026-09-26 15:31:21` (4)

## Mẫu dữ liệu

| id | code | type | value | min_order_value | max_discount | starts_at | expires_at | usage_limit | per_user_limit | status | description | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | SALETO500K | percent | 10 | 500000 | 69000 | NULL | 2026-12-31 23:59:59 | NULL | 1 | active | Giảm 10% cho đơn hàng giá trị tối thiểu 500K. Mã giảm tối đa 69K. | 2026-09-26 15:31:21 | 2026-09-26 15:31:21
| 2 | SALE9K | fixed | 9000 | 99000 | NULL | NULL | 2026-12-31 23:59:59 | NULL | 1 | active | Mã giảm 9K cho đơn hàng tối thiểu 99K. Tối đa 1 mã giảm giá/đơn hàng. | 2026-09-26 15:31:21 | 2026-09-26 15:31:21
| 3 | SALE22K | fixed | 22000 | 222000 | NULL | NULL | 2026-12-31 23:59:59 | NULL | 1 | active | Mã giảm 22k cho đơn hàng tối thiểu 222K. Tối đa 1 mã giảm giá/đơn hàng. | 2026-09-26 15:31:21 | 2026-09-26 15:31:21
| 4 | SALETO300K | percent | 8 | 300000 | 30000 | NULL | 2026-12-31 23:59:59 | NULL | 1 | active | Giảm 8% cho đơn hàng giá trị tối thiểu 300K. Mã giảm tối đa 30K. | 2026-09-26 15:31:21 | 2026-09-26 15:31:21
