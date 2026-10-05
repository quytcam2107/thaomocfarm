# Bảng `promotions`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-05T11:13:39+00:00

## Thông tin chung

- Số bản ghi: **1**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/promotions.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `type` | enum('flash_sale','campaign') | NO | *NULL* | - | - |
| `name` | varchar(255) | NO | *NULL* | - | - |
| `description` | text | YES | *NULL* | - | - |
| `start_at` | timestamp | NO | *NULL* | - | - |
| `end_at` | timestamp | NO | *NULL* | - | - |
| `status` | enum('scheduled','active','ended','cancelled') | NO | scheduled | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| promotions_status_start_at_end_at_index |  | btree | `status`, `start_at`, `end_at` |

## Thống kê dữ liệu

- `id`: min = 1, max = 1 — giá trị phổ biến: `1` (1)
- `type`: giá trị phổ biến: `flash_sale` (1)
- `name`: giá trị phổ biến: `Giá siêu hời` (1)
- `description`: giá trị phổ biến: `Deal sốc giới hạn slot mỗi ngày cho đặc sản Tây Bắc.` (1)
- `start_at`: min = 2026-10-01 00:00:00, max = 2026-10-01 00:00:00 — giá trị phổ biến: `2026-10-01 00:00:00` (1)
- `end_at`: min = 2026-10-01 23:59:59, max = 2026-10-01 23:59:59 — giá trị phổ biến: `2026-10-01 23:59:59` (1)
- `status`: giá trị phổ biến: `active` (1)
- `created_at`: min = 2026-09-22 11:21:08, max = 2026-09-22 11:21:08 — giá trị phổ biến: `2026-09-22 11:21:08` (1)
- `updated_at`: min = 2026-09-22 11:21:08, max = 2026-09-22 11:21:08 — giá trị phổ biến: `2026-09-22 11:21:08` (1)

## Mẫu dữ liệu

| id | type | name | description | start_at | end_at | status | created_at | updated_at
|---|---|---|---|---|---|---|---|---|
| 1 | flash_sale | Giá siêu hời | Deal sốc giới hạn slot mỗi ngày cho đặc sản Tây Bắc. | 2026-10-01 00:00:00 | 2026-10-01 23:59:59 | active | 2026-09-22 11:21:08 | 2026-09-22 11:21:08
