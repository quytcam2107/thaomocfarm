# Bảng `settings`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:23+00:00

## Thông tin chung

- Số bản ghi: **0**

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `key` | varchar(255) | NO | *NULL* | - | - |
| `value` | text | YES | *NULL* | - | - |
| `group_name` | varchar(255) | NO | general | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| settings_group_name_index |  | btree | `group_name` |
| settings_key_unique | ✔ | btree | `key` |

## Mẫu dữ liệu

_Bảng trống — chưa có bản ghi nào._
