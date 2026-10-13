# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-13T03:17:17+00:00

## Thông tin chung

- Số bản ghi: **5**
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

- `id`: min = 7, max = 83 — giá trị phổ biến: `7` (1), `8` (1), `61` (1), `82` (1), `83` (1)
- `cart_id`: min = 32, max = 321 — giá trị phổ biến: `32` (2), `321` (2), `217` (1)
- `product_id`: min = 1, max = 4 — giá trị phổ biến: `3` (2), `1` (1), `2` (1), `4` (1)
- `product_variant_id`: min = 1, max = 22 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `22` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `1` (2), `13` (1), `8` (1), `3` (1)
- `created_at`: min = 2026-09-24 04:28:35, max = 2026-10-12 10:37:50 — giá trị phổ biến: `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-10-04 14:44:18` (1), `2026-10-12 09:21:29` (1), `2026-10-12 10:37:50` (1)
- `updated_at`: min = 2026-09-24 04:28:42, max = 2026-10-12 10:37:50 — giá trị phổ biến: `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-10-07 08:32:01` (1), `2026-10-12 09:21:29` (1), `2026-10-12 10:37:50` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 61 | 217 | 1 | 1 | 3 | 2026-10-04 14:44:18 | 2026-10-07 08:32:01
| 82 | 321 | 2 | 2 | 1 | 2026-10-12 09:21:29 | 2026-10-12 09:21:29
| 83 | 321 | 3 | 22 | 1 | 2026-10-12 10:37:50 | 2026-10-12 10:37:50
