# Bảng `product_images`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:44+00:00

## Thông tin chung

- Số bản ghi: **39**
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

- `id`: min = 1, max = 41
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `7` (6), `4` (5), `6` (5), `8` (5), `1` (4), `2` (4), `5` (4), `3` (3), `9` (3)
- `alt`: giá trị phổ biến: `Trà Hoa Atiso Sấy Khô Nguyên Nụ` (6), `NULL` (6), `Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông.` (5), `Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt` (5), `Kỷ tử đỏ, câu kỷ tử` (4), `Thịt trâu gác bếp` (3), `Củ tam thất bắc khô loại 1` (3), `Nụ tam thất bắc` (2), `Trà nụ hoa tam thất bắc` (2), `Củ tam thất bắc khô` (1), `Táo đỏ sấy khô` (1), `Trà hoa cúc chi Hưng Yên sấy lạnh nguyên bông` (1)
- `sort_order`: min = 0, max = 5 — giá trị phổ biến: `1` (9), `2` (9), `3` (9), `4` (6), `0` (4), `5` (2)
- `is_cover`: min = 0, max = 1 — giá trị phổ biến: `0` (30), `1` (9)
- `created_at`: min = 2026-09-22 11:21:09, max = 2026-10-04 15:55:22 — giá trị phổ biến: `NULL` (13), `2026-09-22 11:21:09` (4), `2026-09-22 21:37:05` (4), `2026-10-04 15:55:22` (4), `2026-09-25 17:47:18` (3), `2026-10-02 22:58:58` (2), `2026-10-03 18:16:55` (2), `2026-09-22 21:02:38` (1), `2026-10-03 20:50:49` (1), `2026-10-03 20:51:16` (1), `2026-10-04 13:00:16` (1), `2026-10-04 13:02:46` (1), `2026-10-04 13:07:04` (1), `2026-10-04 13:09:43` (1)
- `updated_at`: min = 2026-09-22 11:21:09, max = 2026-10-04 15:55:22 — giá trị phổ biến: `NULL` (13), `2026-09-22 11:21:09` (4), `2026-09-22 21:37:05` (4), `2026-10-04 15:55:22` (4), `2026-09-25 17:47:18` (3), `2026-10-02 22:58:58` (2), `2026-10-03 18:16:55` (2), `2026-09-22 21:02:41` (1), `2026-10-03 20:50:49` (1), `2026-10-03 20:51:16` (1), `2026-10-04 13:00:16` (1), `2026-10-04 13:02:46` (1), `2026-10-04 13:07:04` (1), `2026-10-04 13:09:46` (1)

## Mẫu dữ liệu

| id | product_id | path | thumb_path | alt | sort_order | is_cover | created_at | updated_at
|---|---|---|---|---|---|---|---|---|
| 1 | 1 | products/tam-that-say-kho.png | NULL | Củ tam thất bắc khô | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 2 | 2 | products/nu-tam-that.png | NULL | Nụ tam thất bắc | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 3 | 3 | products/thit-trau.png | NULL | Thịt trâu gác bếp | 1 | 1 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 4 | 4 | products/tao-do/tao-do-01.png | NULL | Táo đỏ sấy khô | 2 | 0 | 2026-09-22 11:21:09 | 2026-09-22 11:21:09
| 5 | 2 | products/nu-tam-that/nu-tam-that-1.png | NULL | Nụ tam thất bắc | 2 | 0 | 2026-09-22 21:02:38 | 2026-09-22 21:02:41
| 6 | 5 | products/ky-tu/ky-tu-do.png | NULL | Kỷ tử đỏ, câu kỷ tử | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 7 | 6 | products/tra-hoa-hong/tra-hoa-hong.png | NULL | Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông. | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 8 | 7 | products/tra-hoa-atiso/tra-hoa-atiso.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 9 | 8 | products/dau-tam/dau-tam-say-kho.png | NULL | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | 0 | 1 | 2026-09-22 21:37:05 | 2026-09-22 21:37:05
| 10 | 9 | products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png | NULL | Trà hoa cúc chi Hưng Yên sấy lạnh nguyên bông | 1 | 1 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
| 11 | 9 | products/tra-hoa-cuc-chi/pha-tra-hoa-cuc-chi.png | NULL | NULL | 2 | 0 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
| 12 | 9 | products/tra-hoa-cuc-chi/about.png | NULL | NULL | 3 | 0 | 2026-09-25 17:47:18 | 2026-09-25 17:47:18
| 13 | 1 | products/cu-tam-that/cu-tam-that-bac-kho-1.png | NULL | Củ tam thất bắc khô loại 1 | 2 | 0 | 2026-10-02 22:58:58 | 2026-10-02 22:58:58
| 14 | 1 | products/cu-tam-that/cu-tam-that-bac-kho-2.png | NULL | Củ tam thất bắc khô loại 1 | 3 | 0 | 2026-10-02 22:58:58 | 2026-10-02 22:58:58
| 16 | 1 | products/cu-tam-that/cu-tam-that-bac-kho-3.png | NULL | Củ tam thất bắc khô loại 1 | 4 | 0 | NULL | NULL
| 17 | 2 | products/nu-tam-that/tra-nu-hoa-tam-that.png | NULL | Trà nụ hoa tam thất bắc | 4 | 0 | 2026-10-03 18:16:55 | 2026-10-03 18:16:55
| 18 | 2 | products/nu-tam-that/nu-tam-that-2.png | NULL | Trà nụ hoa tam thất bắc | 3 | 0 | 2026-10-03 18:16:55 | 2026-10-03 18:16:55
| 19 | 4 | products/tao-do.png | NULL | NULL | 1 | 1 | NULL | NULL
| 20 | 4 | products/tao-do/tao-do-02.png | NULL | NULL | 3 | 0 | NULL | NULL
| 21 | 4 | products/tao-do/tao-do-03.png | NULL | NULL | 4 | 0 | NULL | NULL
| 22 | 4 | products/tao-do/tao-do-04.png | NULL | NULL | 5 | 0 | NULL | NULL
| 23 | 3 | products/thit-trau/thit-trau-1.png | NULL | Thịt trâu gác bếp | 2 | 0 | 2026-10-03 20:50:49 | 2026-10-03 20:50:49
| 24 | 3 | products/thit-trau/thit-trau-2.png | NULL | Thịt trâu gác bếp | 3 | 0 | 2026-10-03 20:51:16 | 2026-10-03 20:51:16
| 25 | 5 | products/ky-tu/huong-dan-pha-ky-tu.png | NULL | Kỷ tử đỏ, câu kỷ tử | 1 | 0 | NULL | NULL
| 26 | 5 | products/ky-tu/ky-tu-do-1.png | NULL | Kỷ tử đỏ, câu kỷ tử | 2 | 0 | NULL | NULL
| 27 | 5 | products/ky-tu/ky-tu-do-2.png | NULL | Kỷ tử đỏ, câu kỷ tử | 3 | 0 | NULL | NULL
| 28 | 6 | products/tra-hoa-hong/tra-hoa-hong-1.png | NULL | Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông. | 1 | 0 | 2026-10-04 13:00:16 | 2026-10-04 13:00:16
| 29 | 6 | products/tra-hoa-hong/tra-hoa-hong-2.png | NULL | Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông. | 2 | 0 | 2026-10-04 13:02:46 | 2026-10-04 13:02:46
| 30 | 6 | products/tra-hoa-hong/tra-hoa-hong-3.png | NULL | Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông. | 3 | 0 | 2026-10-04 13:07:04 | 2026-10-04 13:07:04
| 31 | 6 | products/tra-hoa-hong/tra-hoa-hong-4.png | NULL | Trà Hoa Hồng Sấy Khô Nhiệt Độ Thấp, Hoa Hồng Nguyên Bông. | 4 | 0 | 2026-10-04 13:09:43 | 2026-10-04 13:09:46
| 32 | 7 | products/tra-hoa-atiso/tra-hoa-atiso-1.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 1 | 0 | NULL | NULL
| 33 | 7 | products/tra-hoa-atiso/tra-hoa-atiso-2.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 2 | 0 | NULL | NULL
| 34 | 7 | products/tra-hoa-atiso/tra-hoa-atiso-3.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 3 | 0 | NULL | NULL
| 35 | 7 | products/tra-hoa-atiso/tra-hoa-atiso-4.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 4 | 0 | NULL | NULL
| 36 | 7 | products/tra-hoa-atiso/tra-hoa-atiso-5.png | NULL | Trà Hoa Atiso Sấy Khô Nguyên Nụ | 5 | 0 | NULL | NULL
| 38 | 8 | products/dau-tam/dau-tam-say-kho-1.png | NULL | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | 1 | 0 | 2026-10-04 15:55:22 | 2026-10-04 15:55:22
| 39 | 8 | products/dau-tam/dau-tam-say-kho-2.png | NULL | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | 2 | 0 | 2026-10-04 15:55:22 | 2026-10-04 15:55:22
| 40 | 8 | products/dau-tam/dau-tam-say-kho-3.png | NULL | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | 3 | 0 | 2026-10-04 15:55:22 | 2026-10-04 15:55:22
| 41 | 8 | products/dau-tam/dau-tam-say-kho-4.png | NULL | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | 4 | 0 | 2026-10-04 15:55:22 | 2026-10-04 15:55:22
