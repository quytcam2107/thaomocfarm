# Bảng `order_items`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-01T04:24:22+00:00

## Thông tin chung

- Số bản ghi: **7**
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

- `id`: min = 1, max = 7 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1)
- `order_id`: min = 1, max = 3 — giá trị phổ biến: `1` (3), `3` (3), `2` (1)
- `product_id`: min = 1, max = 9 — giá trị phổ biến: `5` (2), `1` (1), `2` (1), `3` (1), `6` (1), `9` (1)
- `product_variant_id`: min = 1, max = 10 — giá trị phổ biến: `5` (2), `1` (1), `2` (1), `3` (1), `6` (1), `10` (1)
- `name_snapshot`: giá trị phổ biến: `Giảo Cổ Lam Sạch Túi 500g` (2), `Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn - Phơi Khô Nguyên Chất 100%` (1), `Mắc Khén Rang Thơm Túi 200g` (1), `Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi [Tặng Chấm Chéo]` (1), `Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất [Giá Tốt]` (1), `Trà Hoa Cúc Chi Sấy Lạnh Nguyên Bông [Chuẩn Organic]` (1)
- `sku_snapshot`: giá trị phổ biến: `TMF-GCL-001-DEF` (2), `TMF-TT-001-500G` (1), `TMF-MK-001-DEF` (1), `TMF-TGB-001-500G` (1), `TMF-TT-002-500G` (1), `TMF-THC-001-500G` (1)
- `image_snapshot`: giá trị phổ biến: `http://localhost/assets/images/placeholder.svg` (3), `http://localhost/assets/images/products/flash-cu-tam-that.jpg` (1), `http://localhost/assets/images/products/flash-thit-trau.jpg` (1), `http://localhost/assets/images/products/nu-tam-that/nu-tam-that.png` (1), `http://localhost/assets/images/products/tra-hoa-cuc-chi/tra-hoa-cuc-chi.png` (1)
- `price`: min = 85000, max = 1000000 — giá trị phổ biến: `180000` (2), `1000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1)
- `qty`: min = 1, max = 2 — giá trị phổ biến: `1` (6), `2` (1)
- `subtotal`: min = 85000, max = 2000000 — giá trị phổ biến: `180000` (2), `2000000` (1), `85000` (1), `145000` (1), `230000` (1), `150000` (1)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-09-25 04:24:20` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-09-26 09:40:05 — giá trị phổ biến: `2026-09-25 04:15:13` (3), `2026-09-26 09:40:05` (3), `2026-09-25 04:24:20` (1)

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
