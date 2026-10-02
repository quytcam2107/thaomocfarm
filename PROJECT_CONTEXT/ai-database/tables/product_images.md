# Bảng `product_images`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-02T10:14:53+00:00

## Thông tin chung

- Số bản ghi: **12**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/product_images.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `path` | varchar(255) | NO | *NULL* | - | - |
| `thumb_path` | varchar(255) | YES | *NULL* | - | - |
| `alt` | varchar(255) | YES | *NULL* | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `is_cover` | tinyint(1) | NO | 0 | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| product_images_product_id_index |  | btree | `product_id` |
| product_images_product_id_sort_order_index |  | btree | `product_id`, `sort_order` |

## Thống kê dữ liệu

- `id`: min = 1, max = 12 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1), `10` (1), `11` (1), `12` (1)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `9` (3), `2` (2), `1` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1)
- `path`: giá trị phổ biến: `placeholder.svg` (2), `products/tam-that-say-kho.png` (1), `products/nu-tam-that.png` (1), `products/thit-trau.png` (1), `products/tao-do.png` (1), `products/nu-tam-that/nu-tam-that-2.png` (1), `products/ky-tu/ky-tu-do.png` (1), `products/ha-thu-o/ha-thu-o.png` (1), `products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png` (1), `products/tra-hoa-cuc-chi/pha-tra-hoa-cuc-chi.png` (1), `products/tra-hoa-cuc-chi/about.png` (1)
- `alt`: giá trị phổ biến: `Nụ tam thất bắc` (2), `NULL` (2), `Củ tam thất bắc khô` (1), `Thịt trâu gác bếp` (1), `Táo đỏ sấy khô` (1), `Giảo Cổ Lam` (1), `Mắc Khén` (1), `Mật Ong Bạc Hà` (1), `Lạp Xưởng` (1), `Trà hoa cúc chi Hưng Yên sấy lạnh nguyên bông` (1)
- `sort_order`: min = 0, max = 3 — giá trị phổ biến: `1` (5), `0` (4), `2` (2), `3` (1)
- `is_cover`: min = 0, max = 1 — giá trị phổ biến: `1` (9), `0` (3)
- `created_at`: min = 2026-09-22 11:21:09, max = 2026-09-25 17:47:18 — giá trị phổ biến: `2026-09-22 11:21:09` (4), `2026-09-22 21:37:05` (4), `2026-09-25 17:47:18` (3), `2026-09-22 21:02:38` (1)
- `updated_at`: min = 2026-09-22 11:21:09, max = 2026-09-25 17:47:18 — giá trị phổ biến: `2026-09-22 11:21:09` (4), `2026-09-22 21:37:05` (4), `2026-09-25 17:47:18` (3), `2026-09-22 21:02:41` (1)

## Mẫu dữ liệu

| id | product_id | path | thumb_path | alt | sort_order | is_cover | created_at | updated_at
|---|---|---|---|---|---|---|---|---|
| 1 | 1 | products/tam-that-say-kho.png | NULL | Củ tam thất bắc khô | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 2 | 2 | products/nu-tam-that.png | NULL | Nụ tam thất bắc | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 3 | 3 | products/thit-trau.png | NULL | Thịt trâu gác bếp | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 4 | 4 | products/tao-do.png | NULL | Táo đỏ sấy khô | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 5 | 2 | products/nu-tam-that/nu-tam-that-2.png | NULL | Nụ tam thất bắc | 2 | 0 | 2026-09-22 21:02:38 | 2026-09-22 21:02:41
| 6 | 5 | products/ky-tu/ky-tu-do.png | NULL | Giảo Cổ Lam | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 7 | 6 | products/ha-thu-o/ha-thu-o.png | NULL | Mắc Khén | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 8 | 7 | placeholder.svg | NULL | Mật Ong Bạc Hà | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 9 | 8 | placeholder.svg | NULL | Lạp Xưởng | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 10 | 9 | products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png | NULL | Trà hoa cúc chi Hưng Yên sấy lạnh nguyên bông | 1 | 1 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
| 11 | 9 | products/tra-hoa-cuc-chi/pha-tra-hoa-cuc-chi.png | NULL | NULL | 2 | 0 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
| 12 | 9 | products/tra-hoa-cuc-chi/about.png | NULL | NULL | 3 | 0 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
