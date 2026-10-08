# Bảng `order_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T08:53:41+00:00

## Thông tin chung

- Số bản ghi: **28**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/order_items.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `order_id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | YES | *NULL* | - | - |
| `product_variant_id` | bigint unsigned | YES | *NULL* | - | - |
| `name_snapshot` | varchar(255) | NO | *NULL* | - | - |
| `sku_snapshot` | varchar(255) | NO | *NULL* | - | - |
| `image_snapshot` | varchar(255) | YES | *NULL* | - | - |
| `price` | int unsigned | NO | *NULL* | - | - |
| `qty` | smallint unsigned | NO | *NULL* | - | - |
| `subtotal` | int unsigned | NO | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| order_items_order_id_index |  | btree | `order_id` |
| order_items_product_id_index |  | btree | `product_id` |
| order_items_product_variant_id_index |  | btree | `product_variant_id` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 32
- `order_id`: min = 1, max = 13 — giá trị phổ biến: `7` (4), `1` (3), `3` (3), `4` (3), `5` (3), `6` (3), `8` (3), `11` (2), `2` (1), `9` (1), `12` (1), `13` (1)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `1` (8), `2` (5), `4` (5), `3` (4), `5` (2), `6` (1), `7` (1), `8` (1), `9` (1)
- `product_variant_id`: min = 1, max = 11 — giá trị phổ biến: `1` (8), `2` (5), `3` (4), `4` (4), `5` (2), `6` (1), `7` (1), `8` (1), `10` (1), `11` (1)
- `name_snapshot`: giá trị phổ biến: `Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100%` (8), `Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt]` (5), `Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà` (5), `Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo]` (4), `Giảo Cổ Lam Sạch Túi 500g` (2), `Mắc Khén Rang Thơm Túi 200g` (1), `Trà Hoa Cúc Chi Sấy Lạnh Nguyên Bông [Chuẩn Organic]` (1), `Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt` (1), `Trà Hoa Atiso Sấy Khô Nguyên Nụ` (1)
- `sku_snapshot`: giá trị phổ biến: `TMF-TT-001-500G` (8), `TMF-TT-002-500G` (5), `TMF-TGB-001-500G` (4), `TMF-TD-001-500G` (4), `TMF-GCL-001-DEF` (2), `TMF-MK-001-DEF` (1), `TMF-THC-001-500G` (1), `TMF-TD-001-1KG` (1), `TMF-LX-001-DEF` (1), `TMF-MO-001-DEF` (1)
- `image_snapshot`: giá trị phổ biến: `http://localhost/assets/images/products/tam-that-say-kho.png` (7), `http://localhost/assets/images/products/tao-do.png` (4), `http://localhost/assets/images/placeholder.svg` (3), `http://localhost/assets/images/products/nu-tam-that.png` (3), `http://localhost/assets/images/products/thit-trau.png` (3), `http://localhost/assets/images/products/flash-cu-tam-that.jpg` (1), `http://localhost/assets/images/products/flash-thit-trau.jpg` (1), `http://localhost/assets/images/products/nu-tam-that/nu-tam-that.png` (1), `http://localhost/assets/images/products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png` (1), `http://localhost/assets/images/products/dau-tam/dau-tam-say-kho.png` (1), `http://192.168.1.5/assets/images/products/nu-tam-that.png` (1), `http://192.168.1.5/assets/images/products/tao-do.png` (1), `http://192.168.1.5/assets/images/products/tra-hoa-atiso/tra-hoa-atiso.png` (1)
- `price`: min = 85000, max = 1000000 — giá trị phổ biến: `568000` (6), `97500` (4), `94300` (3), `180000` (2), `92000` (2), `90850` (2), `1000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1), `600000` (1), `350000` (1), `220000` (1), `450000` (1)
- `qty`: min = 1, max = 4 — giá trị phổ biến: `1` (17), `2` (5), `3` (5), `4` (1)
- `subtotal`: min = 85000, max = 2000000 — giá trị phổ biến: `97500` (3), `568000` (3), `180000` (2), `92000` (2), `1704000` (2), `188600` (2), `2000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1), `1800000` (1), `94300` (1), `1400000` (1), `1136000` (1), `220000` (1), `292500` (1), `90850` (1), `900000` (1), `272550` (1)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-10-08 04:44:08 — giá trị phổ biến: `2026-10-04 10:32:24` (4), `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-10-01 08:38:38` (3), `2026-10-01 09:02:58` (3), `2026-10-04 05:30:34` (3), `2026-10-04 14:43:50` (3), `2026-10-08 03:41:53` (2), `2026-09-25 04:24:20` (1), `2026-10-05 13:00:10` (1), `2026-10-08 04:42:13` (1), `2026-10-08 04:44:08` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-10-08 04:44:08 — giá trị phổ biến: `2026-10-04 10:32:24` (4), `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-10-01 08:38:38` (3), `2026-10-01 09:02:58` (3), `2026-10-04 05:30:34` (3), `2026-10-04 14:43:50` (3), `2026-10-08 03:41:53` (2), `2026-09-25 04:24:20` (1), `2026-10-05 13:00:10` (1), `2026-10-08 04:42:13` (1), `2026-10-08 04:44:08` (1)

## Mẫu dữ liệu

| id | order_id | product_id | product_variant_id | name_snapshot | sku_snapshot | image_snapshot | price | qty | subtotal | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/flash-cu-tam-that.jpg | 1000000 | 2 | 2000000 | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 2 | 1 | 5 | 5 | Giảo Cổ Lam Sạch Túi 500g | TMF-GCL-001-DEF | http://localhost/assets/images/placeholder.svg | 180000 | 1 | 180000 | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 3 | 1 | 6 | 6 | Mắc Khén Rang Thơm Túi 200g | TMF-MK-001-DEF | http://localhost/assets/images/placeholder.svg | 85000 | 1 | 85000 | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 4 | 2 | 3 | 3 | Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo] | TMF-TGB-001-500G | http://localhost/assets/images/products/flash-thit-trau.jpg | 145000 | 1 | 145000 | 2026-09-25 04:24:20 | 2026-09-25 04:24:20
| 5 | 3 | 2 | 2 | Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt] | TMF-TT-002-500G | http://localhost/assets/images/products/nu-tam-that/nu-tam-that.png | 230000 | 1 | 230000 | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 6 | 3 | 5 | 5 | Giảo Cổ Lam Sạch Túi 500g | TMF-GCL-001-DEF | http://localhost/assets/images/placeholder.svg | 180000 | 1 | 180000 | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 7 | 3 | 9 | 10 | Trà Hoa Cúc Chi Sấy Lạnh Nguyên Bông [Chuẩn Organic] | TMF-THC-001-500G | http://localhost/assets/images/products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png | 150000 | 1 | 150000 | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 8 | 4 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 600000 | 3 | 1800000 | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 9 | 4 | 2 | 2 | Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt] | TMF-TT-002-500G | http://localhost/assets/images/products/nu-tam-that.png | 97500 | 1 | 97500 | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 10 | 4 | 4 | 4 | Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà | TMF-TD-001-500G | http://localhost/assets/images/products/tao-do.png | 92000 | 1 | 92000 | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 11 | 5 | 2 | 2 | Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt] | TMF-TT-002-500G | http://localhost/assets/images/products/nu-tam-that.png | 97500 | 1 | 97500 | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 12 | 5 | 3 | 3 | Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo] | TMF-TGB-001-500G | http://localhost/assets/images/products/thit-trau.png | 94300 | 1 | 94300 | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 13 | 5 | 4 | 4 | Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà | TMF-TD-001-500G | http://localhost/assets/images/products/tao-do.png | 92000 | 1 | 92000 | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 14 | 6 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 3 | 1704000 | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 15 | 6 | 3 | 3 | Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo] | TMF-TGB-001-500G | http://localhost/assets/images/products/thit-trau.png | 94300 | 2 | 188600 | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 16 | 6 | 4 | 11 | Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà | TMF-TD-001-1KG | http://localhost/assets/images/products/tao-do.png | 350000 | 4 | 1400000 | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 17 | 7 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 2 | 1136000 | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 18 | 7 | 2 | 2 | Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt] | TMF-TT-002-500G | http://localhost/assets/images/products/nu-tam-that.png | 97500 | 1 | 97500 | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 19 | 7 | 3 | 3 | Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo] | TMF-TGB-001-500G | http://localhost/assets/images/products/thit-trau.png | 94300 | 2 | 188600 | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 20 | 7 | 8 | 8 | Dâu Tằm Sấy Khô Nguyên Quả Dẻo Ngọt | TMF-LX-001-DEF | http://localhost/assets/images/products/dau-tam/dau-tam-say-kho.png | 220000 | 1 | 220000 | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 21 | 8 | 2 | 2 | Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt] | TMF-TT-002-500G | http://192.168.1.5/assets/images/products/nu-tam-that.png | 97500 | 3 | 292500 | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 22 | 8 | 4 | 4 | Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà | TMF-TD-001-500G | http://192.168.1.5/assets/images/products/tao-do.png | 90850 | 1 | 90850 | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 23 | 8 | 7 | 7 | Trà Hoa Atiso Sấy Khô Nguyên Nụ | TMF-MO-001-DEF | http://192.168.1.5/assets/images/products/tra-hoa-atiso/tra-hoa-atiso.png | 450000 | 2 | 900000 | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 24 | 9 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 1 | 568000 | 2026-10-05 13:00:10 | 2026-10-05 13:00:10
| 29 | 11 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 3 | 1704000 | 2026-10-08 03:41:53 | 2026-10-08 03:41:53
| 30 | 11 | 4 | 4 | Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà | TMF-TD-001-500G | http://localhost/assets/images/products/tao-do.png | 90850 | 3 | 272550 | 2026-10-08 03:41:53 | 2026-10-08 03:41:53
| 31 | 12 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 1 | 568000 | 2026-10-08 04:42:13 | 2026-10-08 04:42:13
| 32 | 13 | 1 | 1 | Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100% | TMF-TT-001-500G | http://localhost/assets/images/products/tam-that-say-kho.png | 568000 | 1 | 568000 | 2026-10-08 04:44:08 | 2026-10-08 04:44:08
