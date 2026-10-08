# Bảng `flash_sale_fake_configs`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T10:53:34+00:00

## Thông tin chung

- Số bản ghi: **4**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/flash_sale_fake_configs.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `target` | int unsigned | NO | 300 | - | - |
| `enabled` | tinyint(1) | NO | 1 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| flash_sale_fake_configs_product_id_unique | ✔ | btree | `product_id` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `product_id`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `target`: min = 281, max = 598 — giá trị phổ biến: `281` (1), `598` (1), `438` (1), `440` (1)
- `enabled`: min = 1, max = 1 — giá trị phổ biến: `1` (4)
- `created_at`: min = 2026-10-08 10:39:01, max = 2026-10-08 10:39:01 — giá trị phổ biến: `2026-10-08 10:39:01` (4)
- `updated_at`: min = 2026-10-08 10:39:01, max = 2026-10-08 10:39:01 — giá trị phổ biến: `2026-10-08 10:39:01` (4)

## Mẫu dữ liệu

| id | product_id | target | enabled | created_at | updated_at
|---|---|---|---|---|---|
| 1 | 1 | 281 | 1 | 2026-10-08 10:39:01 | 2026-10-08 10:39:01
| 2 | 2 | 598 | 1 | 2026-10-08 10:39:01 | 2026-10-08 10:39:01
| 3 | 4 | 438 | 1 | 2026-10-08 10:39:01 | 2026-10-08 10:39:01
| 4 | 3 | 440 | 1 | 2026-10-08 10:39:01 | 2026-10-08 10:39:01
