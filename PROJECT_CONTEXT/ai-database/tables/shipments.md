# Bảng `shipments`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-05T11:13:39+00:00

## Thông tin chung

- Số bản ghi: **8**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/shipments.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `order_id` | bigint unsigned | NO | *NULL* | - | - |
| `carrier` | enum('internal','ghn','ghtk','vtp') | NO | internal | - | - |
| `tracking_code` | varchar(255) | YES | *NULL* | - | - |
| `fee` | int unsigned | NO | 0 | - | - |
| `status` | varchar(255) | NO | pending | - | - |
| `shipped_at` | timestamp | YES | *NULL* | - | - |
| `delivered_at` | timestamp | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| shipments_order_id_unique | ✔ | btree | `order_id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 8 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1)
- `order_id`: min = 1, max = 8 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1)
- `carrier`: giá trị phổ biến: `internal` (8)
- `fee`: min = 0, max = 30000 — giá trị phổ biến: `0` (6), `20000` (1), `30000` (1)
- `status`: giá trị phổ biến: `pending` (8)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-10-04 14:43:50 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1), `2026-10-01 08:38:38` (1), `2026-10-01 09:02:58` (1), `2026-10-04 05:30:34` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-10-04 14:43:50 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1), `2026-10-01 08:38:38` (1), `2026-10-01 09:02:58` (1), `2026-10-04 05:30:34` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1)

## Mẫu dữ liệu

| id | order_id | carrier | tracking_code | fee | status | shipped_at | delivered_at | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | internal | NULL | 0 | pending | NULL | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 2 | 2 | internal | NULL | 20000 | pending | NULL | NULL | 2026-09-25 04:24:20 | 2026-09-25 04:24:20
| 3 | 3 | internal | NULL | 0 | pending | NULL | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 4 | 4 | internal | NULL | 0 | pending | NULL | NULL | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 5 | 5 | internal | NULL | 30000 | pending | NULL | NULL | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 6 | 6 | internal | NULL | 0 | pending | NULL | NULL | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 7 | 7 | internal | NULL | 0 | pending | NULL | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 8 | 8 | internal | NULL | 0 | pending | NULL | NULL | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
