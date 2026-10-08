# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T08:53:41+00:00

## Thông tin chung

- Số bản ghi: **9**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/cart_items.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `cart_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_variant_id` | bigint unsigned | NO | *NULL* | - | - |
| `qty` | smallint unsigned | NO | 1 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| cart_items_cart_id_index |  | btree | `cart_id` |
| cart_items_cart_id_product_variant_id_unique | ✔ | btree | `cart_id`, `product_variant_id` |
| cart_items_product_id_index |  | btree | `product_id` |
| cart_items_product_variant_id_index |  | btree | `product_variant_id` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 7, max = 80 — giá trị phổ biến: `7` (1), `8` (1), `61` (1), `74` (1), `75` (1), `77` (1), `78` (1), `79` (1), `80` (1)
- `cart_id`: min = 32, max = 321 — giá trị phổ biến: `321` (6), `32` (2), `217` (1)
- `product_id`: min = 1, max = 7 — giá trị phổ biến: `1` (2), `3` (2), `4` (2), `5` (1), `6` (1), `7` (1)
- `product_variant_id`: min = 1, max = 7 — giá trị phổ biến: `1` (2), `3` (2), `4` (2), `5` (1), `6` (1), `7` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `1` (5), `13` (1), `8` (1), `3` (1), `2` (1)
- `created_at`: min = 2026-09-24 04:28:35, max = 2026-10-08 05:00:47 — giá trị phổ biến: `2026-10-08 04:47:30` (2), `2026-10-08 04:47:33` (2), `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-10-04 14:44:18` (1), `2026-10-08 04:47:35` (1), `2026-10-08 05:00:47` (1)
- `updated_at`: min = 2026-09-24 04:28:42, max = 2026-10-08 05:01:44 — giá trị phổ biến: `2026-10-08 04:47:30` (2), `2026-10-08 04:47:33` (2), `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-10-07 08:32:01` (1), `2026-10-08 04:47:35` (1), `2026-10-08 05:01:44` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 61 | 217 | 1 | 1 | 3 | 2026-10-04 14:44:18 | 2026-10-07 08:32:01
| 74 | 321 | 4 | 4 | 1 | 2026-10-08 04:47:30 | 2026-10-08 04:47:30
| 75 | 321 | 3 | 3 | 1 | 2026-10-08 04:47:30 | 2026-10-08 04:47:30
| 77 | 321 | 6 | 6 | 1 | 2026-10-08 04:47:33 | 2026-10-08 04:47:33
| 78 | 321 | 5 | 5 | 1 | 2026-10-08 04:47:33 | 2026-10-08 04:47:33
| 79 | 321 | 7 | 7 | 1 | 2026-10-08 04:47:35 | 2026-10-08 04:47:35
| 80 | 321 | 1 | 1 | 2 | 2026-10-08 05:00:47 | 2026-10-08 05:01:44
