# Bảng `review_votes`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T04:43:27+00:00

## Thông tin chung

- Số bản ghi: **3**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/review_votes.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `review_id` | bigint unsigned | NO | *NULL* | - | - |
| `ip_address` | varchar(45) | NO | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| review_votes_review_id_index |  | btree | `review_id` |
| review_votes_review_id_ip_address_unique | ✔ | btree | `review_id`, `ip_address` |

## Thống kê dữ liệu

- `id`: min = 1, max = 6 — giá trị phổ biến: `1` (1), `2` (1), `6` (1)
- `review_id`: min = 1, max = 2 — giá trị phổ biến: `1` (2), `2` (1)
- `ip_address`: giá trị phổ biến: `::1` (2), `127.0.0.1` (1)
- `created_at`: min = 2026-10-05 11:21:35, max = 2026-10-06 07:29:35 — giá trị phổ biến: `2026-10-05 19:12:11` (1), `2026-10-05 11:21:35` (1), `2026-10-06 07:29:35` (1)
- `updated_at`: min = 2026-10-05 11:21:35, max = 2026-10-06 07:29:35 — giá trị phổ biến: `2026-10-05 19:12:11` (1), `2026-10-05 11:21:35` (1), `2026-10-06 07:29:35` (1)

## Mẫu dữ liệu

| id | review_id | ip_address | created_at | updated_at
|---|---|---|---|---|
| 1 | 1 | 127.0.0.1 | 2026-10-05 19:12:11 | 2026-10-05 19:12:11
| 2 | 1 | ::1 | 2026-10-05 11:21:35 | 2026-10-05 11:21:35
| 6 | 2 | ::1 | 2026-10-06 07:29:35 | 2026-10-06 07:29:35
