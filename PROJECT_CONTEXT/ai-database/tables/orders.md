# Bảng `orders`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:43+00:00

## Thông tin chung

- Số bản ghi: **9**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/orders.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `order_number` | varchar(255) | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | YES | *NULL* | - | - |
| `customer_name` | varchar(255) | NO | *NULL* | - | - |
| `customer_phone` | varchar(20) | NO | *NULL* | - | - |
| `customer_email` | varchar(255) | YES | *NULL* | - | - |
| `address_snapshot` | json | NO | *NULL* | - | - |
| `note` | text | YES | *NULL* | - | - |
| `subtotal` | int unsigned | NO | *NULL* | - | - |
| `discount_amount` | int unsigned | NO | 0 | - | - |
| `coupon_id` | bigint unsigned | YES | *NULL* | - | - |
| `coupon_code_snapshot` | varchar(50) | YES | *NULL* | - | - |
| `shipping_fee` | int unsigned | NO | 0 | - | - |
| `total` | int unsigned | NO | *NULL* | - | - |
| `payment_method` | enum('cod','bank_transfer') | NO | *NULL* | - | - |
| `payment_status` | enum('pending','paid','refunded') | NO | pending | - | - |
| `status` | enum('new','confirmed','packing','shipping','delivered','cancelled','returning') | NO | new | - | - |
| `paid_at` | timestamp | YES | *NULL* | - | - |
| `cancelled_at` | timestamp | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| orders_coupon_id_index |  | btree | `coupon_id` |
| orders_customer_phone_created_at_index |  | btree | `customer_phone`, `created_at` |
| orders_order_number_unique | ✔ | btree | `order_number` |
| orders_status_created_at_index |  | btree | `status`, `created_at` |
| orders_user_id_index |  | btree | `user_id` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 9 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1)
- `order_number`: giá trị phổ biến: `TMX-20260925-G7RPB` (1), `TMX-20260925-MGPRW` (1), `TMX-20260926-ZHUIR` (1), `TMX-20261001-OBH6P` (1), `TMX-20261001-UM4P1` (1), `TMX-20261004-4ZCDC` (1), `TMX-20261004-BD1NH` (1), `TMX-20261004-GCXRA` (1), `TMX-20261005-JSTFI` (1)
- `customer_name`: giá trị phổ biến: `Quyết Lưu` (6), `LV Quyết` (2), `Q` (1)
- `customer_phone`: giá trị phổ biến: `0352806324` (8), `0362795897` (1)
- `customer_email`: giá trị phổ biến: `NULL` (7), `quyetluu217@gmail.com` (2)
- `address_snapshot`: giá trị phổ biến: `{"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hồ Chí Minh"}` (3), `{"name": "LV Quyết", "ward": "", "email": "quyetluu217@gmail.com", "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Phú Nhuận", "province": "Hồ Chí Minh"}` (2), `{"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hà Nội"}` (1), `{"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Thủ Đức", "province": "Hà Nội"}` (1), `{"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Phú Nhuận", "province": "Hà Nội"}` (1), `{"name": "Q", "ward": "", "email": null, "phone": "0362795897", "detail": "số 1", "district": "Phú Nhuận", "province": "Đà Nẵng"}` (1)
- `note`: giá trị phổ biến: `NULL` (7), `asdasd` (1), `Gói quà giúp mình` (1)
- `subtotal`: min = 145000, max = 3292600 — giá trị phổ biến: `2265000` (1), `145000` (1), `560000` (1), `1989500` (1), `283800` (1), `3292600` (1), `1642100` (1), `1283350` (1), `568000` (1)
- `discount_amount`: min = 0, max = 69000 — giá trị phổ biến: `0` (4), `9000` (2), `56000` (1), `30000` (1), `69000` (1)
- `coupon_id`: min = 1, max = 4 — giá trị phổ biến: `NULL` (4), `1` (2), `2` (2), `4` (1)
- `coupon_code_snapshot`: giá trị phổ biến: `NULL` (4), `SALETO500K` (2), `SALE9K` (2), `SALETO300K` (1)
- `shipping_fee`: min = 0, max = 30000 — giá trị phổ biến: `0` (7), `20000` (1), `30000` (1)
- `total`: min = 165000, max = 3292600 — giá trị phổ biến: `2265000` (1), `165000` (1), `504000` (1), `1989500` (1), `283800` (1), `3292600` (1), `1633100` (1), `1274350` (1), `499000` (1)
- `payment_method`: giá trị phổ biến: `cod` (9)
- `payment_status`: giá trị phổ biến: `pending` (8), `paid` (1)
- `status`: giá trị phổ biến: `new` (8), `delivered` (1)
- `created_at`: min = 2026-09-25 04:15:13, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1), `2026-10-01 08:38:38` (1), `2026-10-01 09:02:58` (1), `2026-10-04 05:30:34` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1), `2026-10-05 13:00:10` (1)
- `updated_at`: min = 2026-09-25 04:15:13, max = 2026-10-05 13:00:10 — giá trị phổ biến: `2026-09-25 04:15:13` (1), `2026-09-25 04:24:20` (1), `2026-09-26 09:40:05` (1), `2026-10-01 08:38:38` (1), `2026-10-01 09:02:58` (1), `2026-10-04 05:30:34` (1), `2026-10-04 10:32:24` (1), `2026-10-04 14:43:50` (1), `2026-10-05 13:00:10` (1)

## Mẫu dữ liệu

| id | order_number | user_id | customer_name | customer_phone | customer_email | address_snapshot | note | subtotal | discount_amount | coupon_id | coupon_code_snapshot | shipping_fee | total | payment_method | payment_status | status | paid_at | cancelled_at | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | TMX-20260925-G7RPB | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hà Nội"} | asdasd | 2265000 | 0 | NULL | NULL | 0 | 2265000 | cod | pending | new | NULL | NULL | 2026-09-25 04:15:13 | 2026-09-25 04:15:13
| 2 | TMX-20260925-MGPRW | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Thủ Đức", "province": "Hà Nội"} | NULL | 145000 | 0 | NULL | NULL | 20000 | 165000 | cod | pending | new | NULL | NULL | 2026-09-25 04:24:20 | 2026-09-25 04:24:20
| 3 | TMX-20260926-ZHUIR | NULL | LV Quyết | 0352806324 | quyetluu217@gmail.com | {"name": "LV Quyết", "ward": "", "email": "quyetluu217@gmail.com", "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Phú Nhuận", "province": "Hồ Chí Minh"} | NULL | 560000 | 56000 | 1 | SALETO500K | 0 | 504000 | cod | pending | new | NULL | NULL | 2026-09-26 09:40:05 | 2026-09-26 09:40:05
| 4 | TMX-20261001-OBH6P | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hồ Chí Minh"} | NULL | 1989500 | 0 | NULL | NULL | 0 | 1989500 | cod | pending | new | NULL | NULL | 2026-10-01 08:38:38 | 2026-10-01 08:38:38
| 5 | TMX-20261001-UM4P1 | NULL | LV Quyết | 0352806324 | quyetluu217@gmail.com | {"name": "LV Quyết", "ward": "", "email": "quyetluu217@gmail.com", "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Phú Nhuận", "province": "Hồ Chí Minh"} | NULL | 283800 | 30000 | 4 | SALETO300K | 30000 | 283800 | cod | pending | new | NULL | NULL | 2026-10-01 09:02:58 | 2026-10-01 09:02:58
| 6 | TMX-20261004-BD1NH | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Phú Nhuận", "province": "Hà Nội"} | NULL | 3292600 | 0 | NULL | NULL | 0 | 3292600 | cod | pending | new | NULL | NULL | 2026-10-04 05:30:34 | 2026-10-04 05:30:34
| 7 | TMX-20261004-GCXRA | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hồ Chí Minh"} | NULL | 1642100 | 9000 | 2 | SALE9K | 0 | 1633100 | cod | pending | new | NULL | NULL | 2026-10-04 10:32:24 | 2026-10-04 10:32:24
| 8 | TMX-20261004-4ZCDC | NULL | Q | 0362795897 | NULL | {"name": "Q", "ward": "", "email": null, "phone": "0362795897", "detail": "số 1", "district": "Phú Nhuận", "province": "Đà Nẵng"} | NULL | 1283350 | 9000 | 2 | SALE9K | 0 | 1274350 | cod | pending | new | NULL | NULL | 2026-10-04 14:43:50 | 2026-10-04 14:43:50
| 9 | TMX-20261005-JSTFI | NULL | Quyết Lưu | 0352806324 | NULL | {"name": "Quyết Lưu", "ward": "", "email": null, "phone": "0352806324", "detail": "CT3 Yên Nghĩa - Hà Đông", "district": "Quận 3", "province": "Hồ Chí Minh"} | Gói quà giúp mình | 568000 | 69000 | 1 | SALETO500K | 0 | 499000 | cod | paid | delivered | NULL | NULL | 2026-10-05 13:00:10 | 2026-10-05 13:00:10
