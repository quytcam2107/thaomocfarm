# Bảng `product_variants`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-02T10:14:53+00:00

## Thông tin chung

- Số bản ghi: **10**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/product_variants.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `sku` | varchar(255) | NO | *NULL* | - | - |
| `label` | varchar(255) | NO | *NULL* | - | - |
| `price` | int unsigned | NO | *NULL* | - | - |
| `compare_price` | int unsigned | YES | *NULL* | - | - |
| `stock` | int unsigned | NO | 0 | - | - |
| `is_default` | tinyint(1) | NO | 0 | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| product_variants_product_id_index |  | btree | `product_id` |
| product_variants_product_id_is_default_index |  | btree | `product_id`, `is_default` |
| product_variants_product_id_sort_order_index |  | btree | `product_id`, `sort_order` |
| product_variants_sku_unique | ✔ | btree | `sku` |

## Thống kê dữ liệu

- `id`: min = 1, max = 11 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `10` (1), `11` (1)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `4` (2), `1` (1), `2` (1), `3` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1)
- `sku`: giá trị phổ biến: `TMF-GCL-001-DEF` (1), `TMF-LX-001-DEF` (1), `TMF-MK-001-DEF` (1), `TMF-MO-001-DEF` (1), `TMF-TD-001-1KG` (1), `TMF-TD-001-500G` (1), `TMF-TGB-001-500G` (1), `TMF-THC-001-500G` (1), `TMF-TT-001-500G` (1), `TMF-TT-002-500G` (1)
- `label`: giá trị phổ biến: `Túi 500g` (5), `500g` (2), `200g` (1), `1 lít` (1), `Túi 1KG` (1)
- `price`: min = 85000, max = 1000000 — giá trị phổ biến: `1000000` (1), `230000` (1), `145000` (1), `225000` (1), `180000` (1), `85000` (1), `450000` (1), `220000` (1), `150000` (1), `350000` (1)
- `compare_price`: min = 95000, max = 1150000 — giá trị phổ biến: `1150000` (1), `265000` (1), `165000` (1), `255000` (1), `220000` (1), `95000` (1), `520000` (1), `260000` (1), `180000` (1), `400000` (1)
- `stock`: min = 55, max = 299 — giá trị phổ biến: `299` (2), `100` (2), `55` (1), `97` (1), `78` (1), `88` (1), `198` (1), `150` (1)
- `is_default`: min = 0, max = 1 — giá trị phổ biến: `1` (9), `0` (1)
- `sort_order`: min = 0, max = 2 — giá trị phổ biến: `1` (5), `0` (4), `2` (1)
- `created_at`: min = 2026-09-22 11:21:09, max = 2026-09-24 13:00:18 — giá trị phổ biến: `2026-09-22 11:21:09` (4), `2026-09-22 21:37:05` (4), `NULL` (1), `2026-09-24 13:00:18` (1)
- `updated_at`: min = 2026-09-22 21:37:05, max = 2026-10-01 09:02:58 — giá trị phổ biến: `2026-10-01 09:02:58` (3), `2026-09-26 09:40:05` (2), `2026-09-22 21:37:05` (2), `2026-10-01 08:38:38` (1), `2026-09-25 04:15:13` (1), `2026-09-24 13:00:18` (1)

## Mẫu dữ liệu

| id | product_id | sku | label | price | compare_price | stock | is_default | sort_order | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | TMF-TT-001-500G | Túi 500g | 1000000 | 1150000 | 55 | 1 | 1 | 2026-09-22 11:21:09 | 2026-10-01 08:38:38
| 2 | 2 | TMF-TT-002-500G | Túi 500g | 230000 | 265000 | 97 | 1 | 1 | 2026-09-22 11:21:09 | 2026-10-01 09:02:58
| 3 | 3 | TMF-TGB-001-500G | Túi 500g | 145000 | 165000 | 78 | 1 | 1 | 2026-09-22 11:21:09 | 2026-10-01 09:02:58
| 4 | 4 | TMF-TD-001-500G | Túi 500g | 225000 | 255000 | 88 | 1 | 1 | 2026-09-22 11:21:09 | 2026-10-01 09:02:58
| 5 | 5 | TMF-GCL-001-DEF | 500g | 180000 | 220000 | 198 | 1 | 0 | 2026-09-22 21:37:05 | 2026-09-26 09:40:05
| 6 | 6 | TMF-MK-001-DEF | 200g | 85000 | 95000 | 299 | 1 | 0 | 2026-09-22 21:37:05 | 2026-09-25 04:15:13
| 7 | 7 | TMF-MO-001-DEF | 1 lít | 450000 | 520000 | 100 | 1 | 0 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 8 | 8 | TMF-LX-001-DEF | 500g | 220000 | 260000 | 150 | 1 | 0 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 10 | 9 | TMF-THC-001-500G | Túi 500g | 150000 | 180000 | 299 | 1 | 1 | NULL | 2026-09-26 09:40:05
| 11 | 4 | TMF-TD-001-1KG | Túi 1KG | 350000 | 400000 | 100 | 0 | 2 | 2026-09-24 13:00:18 | 2026-09-24 13:00:18
