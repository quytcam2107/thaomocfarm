# Bảng `stock_movements`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:44+00:00

## Thông tin chung

- Số bản ghi: **24**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/stock_movements.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_variant_id` | bigint unsigned | NO | *NULL* | - | - |
| `type` | enum('import','export','adjust','order_reserve','order_release') | NO | *NULL* | - | - |
| `qty` | int | NO | *NULL* | - | - |
| `ref_type` | varchar(255) | YES | *NULL* | - | - |
| `ref_id` | bigint unsigned | YES | *NULL* | - | - |
| `note` | text | YES | *NULL* | - | - |
| `created_by` | bigint unsigned | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| stock_movements_created_by_index |  | btree | `created_by` |
| stock_movements_product_variant_id_created_at_index |  | btree | `product_variant_id`, `created_at` |
| stock_movements_product_variant_id_index |  | btree | `product_variant_id` |
| stock_movements_ref_type_ref_id_index |  | btree | `ref_type`, `ref_id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 24 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1), `10` (1), `11` (1), `12` (1), `13` (1), `14` (1), `15` (1), `16` (1), `17` (1), `18` (1), `19` (1), `20` (1), `21` (1), `22` (1), `23` (1), `24` (1)
- `product_variant_id`: min = 1, max = 11 — giá trị phổ biến: `1` (5), `2` (5), `3` (4), `4` (3), `5` (2), `6` (1), `7` (1), `8` (1), `10` (1), `11` (1)
- `type`: giá trị phổ biến: `export` (24)
- `qty`: min = -4, max = -1 — giá trị phổ biến: `-1` (15), `-2` (5), `-3` (3), `-4` (1)
- `ref_type`: giá trị phổ biến: `order` (24)
- `ref_id`: min = 1, max = 9 — giá trị phổ biến: `7` (4), `1` (3), `3` (3), `4` (3), `5` (3), `6` (3), `8` (3), `2` (1), `9` (1)
- `note`: giá trị phổ biến: `Đặt hàng thành công` (24)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-10-04 10:32:24` (4), `2026-09-25 04:15:13` (3), `2026-10-01 08:38:38` (3), `2026-10-04 05:30:34` (3), `2026-09-26 09:40:05` (3), `2026-10-01 09:02:58` (3), `2026-10-04 14:43:50` (3), `2026-10-05 13:00:10` (1), `2026-09-25 04:24:20` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-10-04 10:32:24` (4), `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-10-01 08:38:38` (3), `2026-10-01 09:02:58` (3), `2026-10-04 05:30:34` (3), `2026-10-04 14:43:50` (3), `2026-09-25 04:24:20` (1), `2026-10-05 13:00:10` (1)

## Mẫu dữ liệu

| id | product_variant_id | type | qty | ref_type | ref_id | note | created_by | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | export | -2 | order | 1 | Đặt hàng thành công | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 2 | 5 | export | -1 | order | 1 | Đặt hàng thành công | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 3 | 6 | export | -1 | order | 1 | Đặt hàng thành công | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 4 | 3 | export | -1 | order | 2 | Đặt hàng thành công | NULL | 2026-09-25 04:24:20 | 2026-09-25 04:24:20
| 5 | 2 | export | -1 | order | 3 | Đặt hàng thành công | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 6 | 5 | export | -1 | order | 3 | Đặt hàng thành công | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 7 | 10 | export | -1 | order | 3 | Đặt hàng thành công | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 8 | 1 | export | -3 | order | 4 | Đặt hàng thành công | NULL | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 9 | 2 | export | -1 | order | 4 | Đặt hàng thành công | NULL | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 10 | 4 | export | -1 | order | 4 | Đặt hàng thành công | NULL | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 11 | 2 | export | -1 | order | 5 | Đặt hàng thành công | NULL | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 12 | 3 | export | -1 | order | 5 | Đặt hàng thành công | NULL | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 13 | 4 | export | -1 | order | 5 | Đặt hàng thành công | NULL | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 14 | 1 | export | -3 | order | 6 | Đặt hàng thành công | NULL | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 15 | 3 | export | -2 | order | 6 | Đặt hàng thành công | NULL | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 16 | 11 | export | -4 | order | 6 | Đặt hàng thành công | NULL | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 17 | 1 | export | -2 | order | 7 | Đặt hàng thành công | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 18 | 2 | export | -1 | order | 7 | Đặt hàng thành công | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 19 | 3 | export | -2 | order | 7 | Đặt hàng thành công | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 20 | 8 | export | -1 | order | 7 | Đặt hàng thành công | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 21 | 2 | export | -3 | order | 8 | Đặt hàng thành công | NULL | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 22 | 4 | export | -1 | order | 8 | Đặt hàng thành công | NULL | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 23 | 7 | export | -2 | order | 8 | Đặt hàng thành công | NULL | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 24 | 1 | export | -1 | order | 9 | Đặt hàng thành công | NULL | 2026-10-05 13:00:10 | 2026-10-05 13:00:10
