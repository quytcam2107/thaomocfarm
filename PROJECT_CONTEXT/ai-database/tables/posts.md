# Bảng `posts`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:22+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `post_category_id` | bigint unsigned | YES | *NULL* | - | - |
| `title` | varchar(255) | NO | *NULL* | - | - |
| `slug` | varchar(255) | NO | *NULL* | - | - |
| `excerpt` | text | YES | *NULL* | - | - |
| `content` | longtext | NO | *NULL* | - | - |
| `cover` | varchar(255) | YES | *NULL* | - | - |
| `reading_minutes` | smallint unsigned | NO | 3 | - | - |
| `status` | enum('draft','published') | NO | draft | - | - |
| `published_at` | timestamp | YES | *NULL* | - | - |
| `view_count` | int unsigned | NO | 0 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| posts_post_category_id_index |  | btree | `post_category_id` |
| posts_slug_unique | ✔ | btree | `slug` |
| posts_status_published_at_index |  | btree | `status`, `published_at` |
| primary | ✔ | btree | `id` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
