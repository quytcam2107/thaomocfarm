# Bảng `search_terms`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-02T10:14:54+00:00

## Thông tin chung

- Số bản ghi: **5**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/search_terms.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `term` | varchar(255) | NO | *NULL* | - | - |
| `hits` | int unsigned | NO | 1 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| search_terms_hits_index |  | btree | `hits` |
| search_terms_term_unique | ✔ | btree | `term` |

## Thống kê dữ liệu

- `id`: min = 1, max = 5 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1)
- `term`: giá trị phổ biến: `củ` (1), `củ tam` (1), `hoa` (1), `nụ` (1), `trà` (1)
- `hits`: min = 2, max = 2 — giá trị phổ biến: `2` (5)
- `created_at`: min = 2026-09-28 03:14:42, max = 2026-10-01 12:47:16 — giá trị phổ biến: `2026-09-28 03:14:42` (1), `2026-09-28 03:20:56` (1), `2026-09-28 04:01:41` (1), `2026-09-28 10:13:07` (1), `2026-10-01 12:47:16` (1)
- `updated_at`: min = 2026-09-28 03:14:42, max = 2026-10-01 12:47:16 — giá trị phổ biến: `2026-09-28 03:14:42` (1), `2026-09-28 03:20:56` (1), `2026-09-28 04:01:41` (1), `2026-09-28 10:13:07` (1), `2026-10-01 12:47:16` (1)

## Mẫu dữ liệu

| id | term | hits | created_at | updated_at
|---|---|---|---|---|
| 1 | nụ | 2 | 2026-09-28 03:14:42 | 2026-09-28 03:14:42
| 2 | hoa | 2 | 2026-09-28 03:20:56 | 2026-09-28 03:20:56
| 3 | củ tam | 2 | 2026-09-28 04:01:41 | 2026-09-28 04:01:41
| 4 | củ | 2 | 2026-09-28 10:13:07 | 2026-09-28 10:13:07
| 5 | trà | 2 | 2026-10-01 12:47:16 | 2026-10-01 12:47:16
