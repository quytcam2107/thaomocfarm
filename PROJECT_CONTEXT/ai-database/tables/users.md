# Bảng `users`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:23+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `name` | varchar(255) | NO | *NULL* | - | - |
| `email` | varchar(255) | NO | *NULL* | - | - |
| `phone` | varchar(20) | YES | *NULL* | - | - |
| `email_verified_at` | timestamp | YES | *NULL* | - | - |
| `password` | varchar(255) | NO | *NULL* | - | - |
| `role` | enum('admin','staff','customer') | NO | customer | - | - |
| `remember_token` | varchar(100) | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| users_email_unique | ✔ | btree | `email` |
| users_phone_unique | ✔ | btree | `phone` |
| users_role_created_at_index |  | btree | `role`, `created_at` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
