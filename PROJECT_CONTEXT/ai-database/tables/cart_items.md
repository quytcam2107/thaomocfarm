# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-04T08:23:26+00:00

## Thông tin chung

- Số bản ghi: **4**
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

- `id`: min = 4, max = 42 — giá trị phổ biến: `4` (1), `7` (1), `8` (1), `42` (1)
- `cart_id`: min = 26, max = 32 — giá trị phổ biến: `26` (2), `32` (2)
- `product_id`: min = 2, max = 4 — giá trị phổ biến: `4` (2), `2` (1), `3` (1)
- `product_variant_id`: min = 2, max = 4 — giá trị phổ biến: `4` (2), `2` (1), `3` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `1` (1), `13` (1), `8` (1), `3` (1)
- `created_at`: min = 2026-09-23 16:22:56, max = 2026-09-28 12:56:32 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-09-28 12:56:32` (1)
- `updated_at`: min = 2026-09-23 16:22:56, max = 2026-09-30 11:07:37 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-09-30 11:07:37` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 4 | 26 | 4 | 4 | 1 | 2026-09-23 16:22:56 | 2026-09-23 16:22:56
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 42 | 26 | 2 | 2 | 3 | 2026-09-28 12:56:32 | 2026-09-30 11:07:37
