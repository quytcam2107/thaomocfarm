# Bảng `reviews`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:23+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | NO | *NULL* | - | - |
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

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
