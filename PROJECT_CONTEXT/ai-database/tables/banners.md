# Bảng `banners`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T02:51:32+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `position` | enum('home_hero','home_mid','category','product') | NO | *NULL* | - | - |
| `title` | varchar(255) | NO | *NULL* | - | - |
| `subtitle` | varchar(255) | YES | *NULL* | - | - |
| `image` | varchar(255) | NO | *NULL* | - | - |
| `link` | varchar(255) | YES | *NULL* | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `status` | enum('active','inactive') | NO | active | - | - |
| `start_at` | timestamp | YES | *NULL* | - | - |
| `end_at` | timestamp | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| banners_position_status_sort_order_index |  | btree | `position`, `status`, `sort_order` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
