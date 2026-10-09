# Bảng `promotion_products`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-09T02:51:34+00:00

## Thông tin chung

- Số bản ghi: **4**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/promotion_products.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `promotion_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_variant_id` | bigint unsigned | YES | *NULL* | - | - |
| `flash_price` | int unsigned | NO | *NULL* | - | - |
| `discount_percent` | smallint unsigned | NO | 0 | - | - |
| `qty_total` | int unsigned | NO | *NULL* | - | - |
| `qty_sold` | int unsigned | NO | 0 | - | - |
| `per_user_limit` | smallint unsigned | NO | 1 | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| promotion_products_product_id_index |  | btree | `product_id` |
| promotion_products_product_variant_id_index |  | btree | `product_variant_id` |
| promotion_products_promotion_id_index |  | btree | `promotion_id` |
| promotion_products_promotion_id_sort_order_index |  | btree | `promotion_id`, `sort_order` |

## Thống kê dữ liệu

- `id`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `promotion_id`: min = 1, max = 1 — giá trị phổ biến: `1` (4)
- `product_id`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `flash_price`: min = 115000, max = 800000 — giá trị phổ biến: `115000` (2), `800000` (1), `150000` (1)
- `discount_percent`: min = 18, max = 35 — giá trị phổ biến: `29` (1), `35` (1), `21` (1), `18` (1)
- `qty_total`: min = 300, max = 300 — giá trị phổ biến: `300` (4)
- `qty_sold`: min = 135, max = 242 — giá trị phổ biến: `242` (1), `163` (1), `135` (1), `151` (1)
- `per_user_limit`: min = 2, max = 3 — giá trị phổ biến: `2` (3), `3` (1)
- `sort_order`: min = 1, max = 4 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1)
- `created_at`: min = 2026-09-22 11:21:09, max = 2026-09-22 11:21:09 — giá trị phổ biến: `2026-09-22 11:21:09` (4)
- `updated_at`: min = 2026-09-22 11:21:09, max = 2026-09-22 11:21:09 — giá trị phổ biến: `2026-09-22 11:21:09` (4)

## Mẫu dữ liệu

| id | promotion_id | product_id | product_variant_id | flash_price | discount_percent | qty_total | qty_sold | per_user_limit | sort_order | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | NULL | 800000 | 29 | 300 | 242 | 2 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 2 | 1 | 2 | NULL | 150000 | 35 | 300 | 163 | 3 | 2 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 3 | 1 | 4 | NULL | 115000 | 21 | 300 | 135 | 2 | 3 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 4 | 1 | 3 | NULL | 115000 | 18 | 300 | 151 | 2 | 4 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
