# Bảng `newsletter_subscribers`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T08:42:58+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `email` | varchar(255) | NO | *NULL* | - | - |
| `status` | enum('subscribed','unsubscribed') | NO | subscribed | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| newsletter_subscribers_email_unique | ✔ | btree | `email` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
