# Bảng `cart_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-02T10:14:53+00:00

## Thông tin chung

- Số bản ghi: **7**
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

- `id`: min = 4, max = 51 — giá trị phổ biến: `4` (1), `7` (1), `8` (1), `42` (1), `48` (1), `49` (1), `51` (1)
- `cart_id`: min = 26, max = 104 — giá trị phổ biến: `104` (3), `26` (2), `32` (2)
- `product_id`: min = 1, max = 4 — giá trị phổ biến: `4` (3), `3` (2), `1` (1), `2` (1)
- `product_variant_id`: min = 1, max = 11 — giá trị phổ biến: `3` (2), `4` (2), `1` (1), `2` (1), `11` (1)
- `qty`: min = 1, max = 13 — giá trị phổ biến: `3` (2), `1` (1), `13` (1), `8` (1), `4` (1), `2` (1)
- `created_at`: min = 2026-09-23 16:22:56, max = 2026-10-01 09:21:30 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:35` (1), `2026-09-24 04:28:44` (1), `2026-09-28 12:56:32` (1), `2026-10-01 09:04:58` (1), `2026-10-01 09:06:40` (1), `2026-10-01 09:21:30` (1)
- `updated_at`: min = 2026-09-23 16:22:56, max = 2026-10-02 08:16:17 — giá trị phổ biến: `2026-09-23 16:22:56` (1), `2026-09-24 04:28:42` (1), `2026-09-24 04:28:50` (1), `2026-09-30 11:07:37` (1), `2026-10-02 03:17:07` (1), `2026-10-01 09:11:41` (1), `2026-10-02 08:16:17` (1)

## Mẫu dữ liệu

| id | cart_id | product_id | product_variant_id | qty | created_at | updated_at
|---|---|---|---|---|---|---|
| 4 | 26 | 4 | 4 | 1 | 2026-09-23 16:22:56 | 2026-09-23 16:22:56
| 7 | 32 | 3 | 3 | 13 | 2026-09-24 04:28:35 | 2026-09-24 04:28:42
| 8 | 32 | 4 | 4 | 8 | 2026-09-24 04:28:44 | 2026-09-24 04:28:50
| 42 | 26 | 2 | 2 | 3 | 2026-09-28 12:56:32 | 2026-09-30 11:07:37
| 48 | 104 | 1 | 1 | 3 | 2026-10-01 09:04:58 | 2026-10-02 03:17:07
| 49 | 104 | 4 | 11 | 4 | 2026-10-01 09:06:40 | 2026-10-01 09:11:41
| 51 | 104 | 3 | 3 | 2 | 2026-10-01 09:21:30 | 2026-10-02 08:16:17
