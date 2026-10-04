# Bảng `wishlists`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-04T08:23:27+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| wishlists_product_id_index |  | btree | `product_id` |
| wishlists_user_id_index |  | btree | `user_id` |
| wishlists_user_id_product_id_unique | ✔ | btree | `user_id`, `product_id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
