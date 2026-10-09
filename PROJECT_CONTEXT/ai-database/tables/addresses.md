# Bảng `addresses`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T08:42:57+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | NO | *NULL* | - | - |
| `full_name` | varchar(255) | NO | *NULL* | - | - |
| `phone` | varchar(20) | NO | *NULL* | - | - |
| `province` | varchar(255) | NO | *NULL* | - | - |
| `district` | varchar(255) | NO | *NULL* | - | - |
| `ward` | varchar(255) | NO | *NULL* | - | - |
| `detail` | varchar(255) | NO | *NULL* | - | - |
| `is_default` | tinyint(1) | NO | 0 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| addresses_user_id_index |  | btree | `user_id` |
| addresses_user_id_is_default_index |  | btree | `user_id`, `is_default` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
