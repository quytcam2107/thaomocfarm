# Bảng `coupon_usages`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:43+00:00

## Thông tin chung

- Số bản ghi: **5**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/coupon_usages.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `coupon_id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | YES | *NULL* | - | - |
| `order_id` | bigint unsigned | YES | *NULL* | - | - |
| `discount_amount` | int unsigned | NO | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| coupon_usages_coupon_id_index |  | btree | `coupon_id` |
| coupon_usages_coupon_id_user_id_index |  | btree | `coupon_id`, `user_id` |
| coupon_usages_order_id_index |  | btree | `order_id` |
| coupon_usages_user_id_index |  | btree | `user_id` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 5 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1)
- `coupon_id`: min = 1, max = 4 — giá trị phổ biến: `1` (2), `2` (2), `4` (1)
- `order_id`: min = 3, max = 9 — giá trị phổ biến: `3` (1), `5` (1), `7` (1), `8` (1), `9` (1)
- `discount_amount`: min = 9000, max = 69000 — giá trị phổ biến: `9000` (2), `56000` (1), `30000` (1), `69000` (1)
- `created_at`: min = 2026-09-26 09:40:05, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-09-26 09:40:05` (1), `2026-10-01 09:02:58` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1), `2026-10-05 13:00:10` (1)
- `updated_at`: min = 2026-09-26 09:40:05, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-09-26 09:40:05` (1), `2026-10-01 09:02:58` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1), `2026-10-05 13:00:10` (1)

## Mẫu dữ liệu

| id | coupon_id | user_id | order_id | discount_amount | created_at | updated_at
|---|---|---|---|---|---|---|
| 1 | 1 | NULL | 3 | 56000 | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 2 | 4 | NULL | 5 | 30000 | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 3 | 2 | NULL | 7 | 9000 | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 4 | 2 | NULL | 8 | 9000 | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 5 | 1 | NULL | 9 | 69000 | 2026-10-05 13:00:10 | 2026-10-05 13:00:10
