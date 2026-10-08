# Bảng `order_status_histories`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T08:53:41+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `order_id` | bigint unsigned | NO | *NULL* | - | - |
| `status` | varchar(255) | NO | *NULL* | - | - |
| `note` | text | YES | *NULL* | - | - |
| `created_by` | bigint unsigned | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| order_status_histories_created_by_index |  | btree | `created_by` |
| order_status_histories_order_id_created_at_index |  | btree | `order_id`, `created_at` |
| order_status_histories_order_id_index |  | btree | `order_id` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
