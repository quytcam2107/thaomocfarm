# Bảng `testimonials`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-05T11:13:39+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `customer_name` | varchar(255) | NO | *NULL* | - | - |
| `customer_location` | varchar(255) | YES | *NULL* | - | - |
| `orders_count` | smallint unsigned | NO | 1 | - | - |
| `rating` | tinyint unsigned | NO | 5 | - | - |
| `content` | text | NO | *NULL* | - | - |
| `avatar` | varchar(255) | YES | *NULL* | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `status` | enum('active','hidden') | NO | active | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
