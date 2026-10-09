# Bảng `flash_sale_fake_configs`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T04:43:26+00:00

## Thông tin chung

- Số bản ghi: **4**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/flash_sale_fake_configs.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `target` | int unsigned | NO | 300 | - | TONG SO SAN PHAM BAN TRONG DOT flash sale (goal ca 24h, du chia deu + thua 1 phan tram van dat goal) |
| `sold_count` | int unsigned | NO | 0 | - | SO LUOT BAN DA FAKE trong chu ky 24h hien tai (reset ve 0 dau ngay) |
| `cycle_date` | date | YES | *NULL* | - | Ngay bat dau chu ky fake hien tai (APP_TZ). Khac hom nay => JS server reset sold_count=0 de chay lai tu dau |
| `last_tick_at` | timestamp | YES | *NULL* | - | Thoi diem tick gan nhat (server-side rate limit ~5 phut/tick) |
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
- `target`: min = 150, max = 250 — giá trị phổ biến: `150` (1), `190` (1), `220` (1), `250` (1)
- `sold_count`: min = 47, max = 79 — giá trị phổ biến: `77` (2), `47` (1), `79` (1)
- `cycle_date`: min = 2026-10-08, max = 2026-10-08 — giá trị phổ biến: `2026-10-08` (4)
- `last_tick_at`: min = 2026-10-08 15:14:49, max = 2026-10-08 15:14:49 — giá trị phổ biến: `2026-10-08 15:14:49` (4)
- `enabled`: min = 1, max = 1 — giá trị phổ biến: `1` (4)
- `created_at`: min = 2026-10-08 10:39:01, max = 2026-10-08 10:39:01 — giá trị phổ biến: `2026-10-08 10:39:01` (4)
- `updated_at`: min = 2026-10-08 15:14:49, max = 2026-10-08 15:14:49 — giá trị phổ biến: `2026-10-08 15:14:49` (4)

## Mẫu dữ liệu

| id | product_id | target | sold_count | cycle_date | last_tick_at | enabled | created_at | updated_at
|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 150 | 47 | 2026-10-08 | 2026-10-08 15:14:49 | 1 | 2026-10-08 10:39:01 | 2026-10-08 15:14:49
| 2 | 2 | 190 | 79 | 2026-10-08 | 2026-10-08 15:14:49 | 1 | 2026-10-08 10:39:01 | 2026-10-08 15:14:49
| 3 | 4 | 220 | 77 | 2026-10-08 | 2026-10-08 15:14:49 | 1 | 2026-10-08 10:39:01 | 2026-10-08 15:14:49
| 4 | 3 | 250 | 77 | 2026-10-08 | 2026-10-08 15:14:49 | 1 | 2026-10-08 10:39:01 | 2026-10-08 15:14:49
