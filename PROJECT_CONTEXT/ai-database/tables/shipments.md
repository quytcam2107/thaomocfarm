# Bảng `shipments`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:23+00:00

## Thông tin chung

- Số bản ghi: **3**
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

- `id`: min = 1, max = 3 — giá trị phổ biến: `1` (1), `2` (1), `3` (1)
- `order_id`: min = 1, max = 3 — giá trị phổ biến: `1` (1), `2` (1), `3` (1)
- `carrier`: giá trị phổ biến: `internal` (3)
- `fee`: min = 0, max = 20000 — giá trị phổ biến: `0` (2), `20000` (1)
- `status`: giá trị phổ biến: `pending` (3)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1)

## Mẫu dữ liệu

| id | order_id | carrier | tracking_code | fee | status | shipped_at | delivered_at | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | internal | NULL | 0 | pending | NULL | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 2 | 2 | internal | NULL | 20000 | pending | NULL | NULL | 2026-09-25 04:24:20 | 2026-09-25 04:24:20
| 3 | 3 | internal | NULL | 0 | pending | NULL | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
