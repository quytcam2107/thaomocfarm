# Bảng `order_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-02T10:14:53+00:00

## Thông tin chung

- Số bản ghi: **13**
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

- `id`: min = 1, max = 13 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1), `10` (1), `11` (1), `12` (1), `13` (1)
- `order_id`: min = 1, max = 5 — giá trị phổ biến: `1` (3), `3` (3), `4` (3), `5` (3), `2` (1)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `2` (3), `1` (2), `3` (2), `4` (2), `5` (2), `6` (1), `9` (1)
- `product_variant_id`: min = 1, max = 10 — giá trị phổ biến: `2` (3), `1` (2), `3` (2), `4` (2), `5` (2), `6` (1), `10` (1)
- `name_snapshot`: giá trị phổ biến: `Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt]` (3), `Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100%` (2), `Giảo Cổ Lam Sạch Túi 500g` (2), `Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo]` (2), `Táo Đỏ Sấy Khô Quả To Loại 1 - Hàng Chuẩn Dẻo Ngọt Nấu Chè Pha Trà` (2), `Mắc Khén Rang Thơm Túi 200g` (1), `Trà Hoa Cúc Chi Sấy Lạnh Nguyên Bông [Chuẩn Organic]` (1)
- `sku_snapshot`: giá trị phổ biến: `TMF-TT-002-500G` (3), `TMF-TT-001-500G` (2), `TMF-GCL-001-DEF` (2), `TMF-TGB-001-500G` (2), `TMF-TD-001-500G` (2), `TMF-MK-001-DEF` (1), `TMF-THC-001-500G` (1)
- `image_snapshot`: giá trị phổ biến: `http://localhost/assets/images/placeholder.svg` (3), `http://localhost/assets/images/products/nu-tam-that.png` (2), `http://localhost/assets/images/products/tao-do.png` (2), `http://localhost/assets/images/products/flash-cu-tam-that.jpg` (1), `http://localhost/assets/images/products/flash-thit-trau.jpg` (1), `http://localhost/assets/images/products/nu-tam-that/nu-tam-that.png` (1), `http://localhost/assets/images/products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png` (1), `http://localhost/assets/images/products/tam-that-say-kho.png` (1), `http://localhost/assets/images/products/thit-trau.png` (1)
- `price`: min = 85000, max = 1000000 — giá trị phổ biến: `180000` (2), `97500` (2), `92000` (2), `1000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1), `600000` (1), `94300` (1)
- `qty`: min = 1, max = 3 — giá trị phổ biến: `1` (11), `2` (1), `3` (1)
- `subtotal`: min = 85000, max = 2000000 — giá trị phổ biến: `180000` (2), `97500` (2), `92000` (2), `2000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1), `1800000` (1), `94300` (1)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-10-01 09:02:58 — giá trị phổ biến: `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-10-01 08:38:38` (3), `2026-10-01 09:02:58` (3), `2026-09-25 04:24:20` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-10-01 09:02:58 — giá trị phổ biến: `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-10-01 08:38:38` (3), `2026-10-01 09:02:58` (3), `2026-09-25 04:24:20` (1)

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
