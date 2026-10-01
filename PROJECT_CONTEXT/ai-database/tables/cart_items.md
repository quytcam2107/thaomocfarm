# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:22+00:00

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

- `id`: min = 4, max = 43 — giá trị phổ biến: `4` (1), `7` (1), `8` (1), `37` (1), `38` (1), `40` (1), `41` (1), `42` (1), `43` (1)
- `cart_id`: min = 26, max = 87 — giá trị phổ biến: `87` (5), `26` (2), `32` (2)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `4` (3), `2` (2), `1` (1), `3` (1), `6` (1), `9` (1)
- `product_variant_id`: min = 1, max = 10 — giá trị phổ biến: `4` (3), `2` (2), `1` (1), `3` (1), `6` (1), `10` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `1` (3), `3` (2), `2` (2), `13` (1), `8` (1)
- `created_at`: min = 2026-09-23 16:22:56, max = 2026-09-30 07:55:22 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-09-26 09:42:55` (1), `2026-09-26 09:42:57` (1), `2026-09-26 14:32:06` (1), `2026-09-28 10:42:28` (1), `2026-09-28 12:56:32` (1), `2026-09-30 07:55:22` (1)
- `updated_at`: min = 2026-09-23 16:22:56, max = 2026-09-30 11:07:37 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-09-26 11:25:31` (1), `2026-09-28 04:43:10` (1), `2026-09-26 14:32:09` (1), `2026-09-28 10:42:28` (1), `2026-09-30 11:07:37` (1), `2026-09-30 07:55:22` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 4 | 26 | 4 | 4 | 1 | 2026-09-23 16:22:56 | 2026-09-23 16:22:56
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 37 | 87 | 1 | 1 | 3 | 2026-09-26 09:42:55 | 2026-09-26 11:25:31
| 38 | 87 | 6 | 6 | 2 | 2026-09-26 09:42:57 | 2026-09-28 04:43:10
| 40 | 87 | 9 | 10 | 2 | 2026-09-26 14:32:06 | 2026-09-26 14:32:09
| 41 | 87 | 4 | 4 | 1 | 2026-09-28 10:42:28 | 2026-09-28 10:42:28
| 42 | 26 | 2 | 2 | 3 | 2026-09-28 12:56:32 | 2026-09-30 11:07:37
| 43 | 87 | 2 | 2 | 1 | 2026-09-30 07:55:22 | 2026-09-30 07:55:22
