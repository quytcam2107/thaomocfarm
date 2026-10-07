# Bảng `provinces`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:44+00:00

## Thông tin chung

- Số bản ghi: **34**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/provinces.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `code` | int unsigned | NO | *NULL* | - | - |
| `name` | varchar(100) | NO | *NULL* | - | - |
| `division_type` | varchar(50) | YES | *NULL* | - | - |
| `codename` | varchar(100) | NO | *NULL* | - | - |
| `phone_code` | varchar(32) | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| provinces_code_unique | ✔ | btree | `code` |
| provinces_codename_unique | ✔ | btree | `codename` |
| provinces_name_index |  | btree | `name` |

## Thống kê dữ liệu

- `id`: min = 35, max = 68
- `code`: min = 1, max = 96
- `division_type`: giá trị phổ biến: `tỉnh` (28), `thành phố trung ương` (6)
- `created_at`: min = 2026-10-07 13:35:28, max = 2026-10-07 13:35:28 — giá trị phổ biến: `2026-10-07 13:35:28` (34)
- `updated_at`: min = 2026-10-07 13:35:28, max = 2026-10-07 13:35:28 — giá trị phổ biến: `2026-10-07 13:35:28` (34)

## Mẫu dữ liệu

| id | code | name | division_type | codename | phone_code | created_at | updated_at
|---|---|---|---|---|---|---|---|
| 35 | 1 | Thành phố Hà Nội | thành phố trung ương | ha_noi | 24 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 36 | 4 | Tỉnh Cao Bằng | tỉnh | cao_bang | 206 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 37 | 8 | Tỉnh Tuyên Quang | tỉnh | tuyen_quang | 207 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 38 | 11 | Tỉnh Điện Biên | tỉnh | dien_bien | 215 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 39 | 12 | Tỉnh Lai Châu | tỉnh | lai_chau | 213 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 40 | 14 | Tỉnh Sơn La | tỉnh | son_la | 212 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 41 | 15 | Tỉnh Lào Cai | tỉnh | lao_cai | 214 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 42 | 19 | Tỉnh Thái Nguyên | tỉnh | thai_nguyen | 208 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 43 | 20 | Tỉnh Lạng Sơn | tỉnh | lang_son | 205 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 44 | 22 | Tỉnh Quảng Ninh | tỉnh | quang_ninh | 203 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 45 | 24 | Tỉnh Bắc Ninh | tỉnh | bac_ninh | 222 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 46 | 25 | Tỉnh Phú Thọ | tỉnh | phu_tho | 210 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 47 | 31 | Thành phố Hải Phòng | thành phố trung ương | hai_phong | 225 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 48 | 33 | Tỉnh Hưng Yên | tỉnh | hung_yen | 221 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 49 | 37 | Tỉnh Ninh Bình | tỉnh | ninh_binh | 229 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 50 | 38 | Tỉnh Thanh Hóa | tỉnh | thanh_hoa | 237 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 51 | 40 | Tỉnh Nghệ An | tỉnh | nghe_an | 238 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 52 | 42 | Tỉnh Hà Tĩnh | tỉnh | ha_tinh | 239 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 53 | 44 | Tỉnh Quảng Trị | tỉnh | quang_tri | 233 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 54 | 46 | Thành phố Huế | thành phố trung ương | hue | 234 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 55 | 48 | Thành phố Đà Nẵng | thành phố trung ương | da_nang | 236 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 56 | 51 | Tỉnh Quảng Ngãi | tỉnh | quang_ngai | 255 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 57 | 52 | Tỉnh Gia Lai | tỉnh | gia_lai | 269 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 58 | 56 | Tỉnh Khánh Hòa | tỉnh | khanh_hoa | 258 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 59 | 66 | Tỉnh Đắk Lắk | tỉnh | dak_lak | 262 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 60 | 68 | Tỉnh Lâm Đồng | tỉnh | lam_dong | 263 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 61 | 75 | Tỉnh Đồng Nai | tỉnh | dong_nai | 251 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 62 | 79 | Thành phố Hồ Chí Minh | thành phố trung ương | ho_chi_minh | 28 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 63 | 80 | Tỉnh Tây Ninh | tỉnh | tay_ninh | 276 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 64 | 82 | Tỉnh Đồng Tháp | tỉnh | dong_thap | 277 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 65 | 86 | Tỉnh Vĩnh Long | tỉnh | vinh_long | 270 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 66 | 91 | Tỉnh An Giang | tỉnh | an_giang | 296 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 67 | 92 | Thành phố Cần Thơ | thành phố trung ương | can_tho | 292 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 68 | 96 | Tỉnh Cà Mau | tỉnh | ca_mau | 290 | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
