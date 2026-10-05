# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-05T11:13:38+00:00

## Thông tin chung

- Số bản ghi: **6**
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

- `id`: min = 7, max = 62 — giá trị phổ biến: `7` (1), `8` (1), `58` (1), `59` (1), `61` (1), `62` (1)
- `cart_id`: min = 32, max = 217 — giá trị phổ biến: `204` (3), `32` (2), `217` (1)
- `product_id`: min = 1, max = 7 — giá trị phổ biến: `3` (2), `1` (1), `2` (1), `4` (1), `7` (1)
- `product_variant_id`: min = 1, max = 7 — giá trị phổ biến: `3` (2), `1` (1), `2` (1), `4` (1), `7` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `2` (2), `13` (1), `8` (1), `5` (1), `1` (1)
- `created_at`: min = 2026-09-24 04:28:35, max = 2026-10-05 02:12:41 — giá trị phổ biến: `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-10-04 10:34:19` (1), `2026-10-04 10:49:29` (1), `2026-10-04 14:44:18` (1), `2026-10-05 02:12:41` (1)
- `updated_at`: min = 2026-09-24 04:28:42, max = 2026-10-05 04:52:07 — giá trị phổ biến: `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-10-05 04:52:07` (1), `2026-10-04 10:49:29` (1), `2026-10-04 14:44:20` (1), `2026-10-05 02:12:43` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 58 | 204 | 2 | 2 | 5 | 2026-10-04 10:34:19 | 2026-10-05 04:52:07
| 59 | 204 | 3 | 3 | 1 | 2026-10-04 10:49:29 | 2026-10-04 10:49:29
| 61 | 217 | 1 | 1 | 2 | 2026-10-04 14:44:18 | 2026-10-04 14:44:20
| 62 | 204 | 7 | 7 | 2 | 2026-10-05 02:12:41 | 2026-10-05 02:12:43
