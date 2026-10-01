# Bảng `coupon_categories`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:22+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `coupon_id` | bigint unsigned | NO | *NULL* | - | - |
| `category_id` | bigint unsigned | NO | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| coupon_categories_category_id_index |  | btree | `category_id` |
| coupon_categories_coupon_id_category_id_unique | ✔ | btree | `coupon_id`, `category_id` |
| coupon_categories_coupon_id_index |  | btree | `coupon_id` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
