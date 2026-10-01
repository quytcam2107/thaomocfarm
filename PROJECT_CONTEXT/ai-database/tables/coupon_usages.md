# Bảng `coupon_usages`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:22+00:00

## Thông tin chung

- Số bản ghi: **1**
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

- `id`: min = 1, max = 1 — giá trị phổ biến: `1` (1)
- `coupon_id`: min = 1, max = 1 — giá trị phổ biến: `1` (1)
- `order_id`: min = 3, max = 3 — giá trị phổ biến: `3` (1)
- `discount_amount`: min = 56000, max = 56000 — giá trị phổ biến: `56000` (1)
- `created_at`: min = 2026-09-26 09:40:05, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-26 09:40:05` (1)
- `updated_at`: min = 2026-09-26 09:40:05, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-26 09:40:05` (1)

## Mẫu dữ liệu

| id | coupon_id | user_id | order_id | discount_amount | created_at | updated_at
|---|---|---|---|---|---|---|
| 1 | 1 | NULL | 3 | 56000 | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
