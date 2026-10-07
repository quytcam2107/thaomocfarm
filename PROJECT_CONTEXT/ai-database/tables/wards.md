# Bảng `wards`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:45+00:00

## Thông tin chung

- Số bản ghi: **3,321**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/wards.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `code` | int unsigned | NO | *NULL* | - | - |
| `name` | varchar(100) | NO | *NULL* | - | - |
| `division_type` | varchar(50) | YES | *NULL* | - | - |
| `codename` | varchar(100) | NO | *NULL* | - | - |
| `province_code` | int unsigned | NO | *NULL* | - | - |
| `province_name` | varchar(100) | YES | *NULL* | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| wards_code_unique | ✔ | btree | `code` |
| wards_codename_index |  | btree | `codename` |
| wards_province_code_codename_index |  | btree | `province_code`, `codename` |
| wards_province_code_index |  | btree | `province_code` |
| wards_province_code_name_index |  | btree | `province_code`, `name` |

## Thống kê dữ liệu

- `id`: min = 1, max = 3321
- `code`: min = 4, max = 32248
- `division_type`: giá trị phổ biến: `xã` (2621), `phường` (687), `đặc khu` (13)
- `province_code`: min = 1, max = 96
- `created_at`: min = 2026-10-07 13:35:28, max = 2026-10-07 13:35:28 — giá trị phổ biến: `2026-10-07 13:35:28` (3321)
- `updated_at`: min = 2026-10-07 13:35:28, max = 2026-10-07 13:35:28 — giá trị phổ biến: `2026-10-07 13:35:28` (3321)

## Mẫu dữ liệu

| id | code | name | division_type | codename | province_code | province_name | created_at | updated_at
|---|---|---|---|---|---|---|---|---|
| 1 | 4 | Phường Ba Đình | phường | phuong_ba_dinh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 2 | 8 | Phường Ngọc Hà | phường | phuong_ngoc_ha | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 3 | 25 | Phường Giảng Võ | phường | phuong_giang_vo | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 4 | 70 | Phường Hoàn Kiếm | phường | phuong_hoan_kiem | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 5 | 82 | Phường Cửa Nam | phường | phuong_cua_nam | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 6 | 91 | Phường Phú Thượng | phường | phuong_phu_thuong | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 7 | 97 | Phường Hồng Hà | phường | phuong_hong_ha | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 8 | 103 | Phường Tây Hồ | phường | phuong_tay_ho | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 9 | 118 | Phường Bồ Đề | phường | phuong_bo_de | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 10 | 127 | Phường Việt Hưng | phường | phuong_viet_hung | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 11 | 136 | Phường Phúc Lợi | phường | phuong_phuc_loi | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 12 | 145 | Phường Long Biên | phường | phuong_long_bien | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 13 | 160 | Phường Nghĩa Đô | phường | phuong_nghia_do | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 14 | 166 | Phường Cầu Giấy | phường | phuong_cau_giay | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 15 | 175 | Phường Yên Hòa | phường | phuong_yen_hoa | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 16 | 190 | Phường Ô Chợ Dừa | phường | phuong_o_cho_dua | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 17 | 199 | Phường Láng | phường | phuong_lang | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 18 | 226 | Phường Văn Miếu - Quốc Tử Giám | phường | phuong_van_mieu_quoc_tu_giam | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 19 | 229 | Phường Kim Liên | phường | phuong_kim_lien | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 20 | 235 | Phường Đống Đa | phường | phuong_dong_da | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 21 | 256 | Phường Hai Bà Trưng | phường | phuong_hai_ba_trung | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 22 | 283 | Phường Vĩnh Tuy | phường | phuong_vinh_tuy | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 23 | 292 | Phường Bạch Mai | phường | phuong_bach_mai | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 24 | 301 | Phường Vĩnh Hưng | phường | phuong_vinh_hung | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 25 | 316 | Phường Định Công | phường | phuong_dinh_cong | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 26 | 322 | Phường Tương Mai | phường | phuong_tuong_mai | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 27 | 328 | Phường Lĩnh Nam | phường | phuong_linh_nam | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 28 | 331 | Phường Hoàng Mai | phường | phuong_hoang_mai | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 29 | 337 | Phường Hoàng Liệt | phường | phuong_hoang_liet | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 30 | 340 | Phường Yên Sở | phường | phuong_yen_so | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 31 | 352 | Phường Phương Liệt | phường | phuong_phuong_liet | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 32 | 364 | Phường Khương Đình | phường | phuong_khuong_dinh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 33 | 367 | Phường Thanh Xuân | phường | phuong_thanh_xuan | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 34 | 376 | Xã Sóc Sơn | xã | xa_soc_son | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 35 | 382 | Xã Kim Anh | xã | xa_kim_anh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 36 | 385 | Xã Trung Giã | xã | xa_trung_gia | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 37 | 430 | Xã Đa Phúc | xã | xa_da_phuc | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 38 | 433 | Xã Nội Bài | xã | xa_noi_bai | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 39 | 454 | Xã Đông Anh | xã | xa_dong_anh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 40 | 466 | Xã Phúc Thịnh | xã | xa_phuc_thinh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 41 | 475 | Xã Thư Lâm | xã | xa_thu_lam | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 42 | 493 | Xã Thiên Lộc | xã | xa_thien_loc | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 43 | 508 | Xã Vĩnh Thanh | xã | xa_vinh_thanh | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 44 | 541 | Xã Phù Đổng | xã | xa_phu_dong | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 45 | 562 | Xã Thuận An | xã | xa_thuan_an | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 46 | 565 | Xã Gia Lâm | xã | xa_gia_lam | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 47 | 577 | Xã Bát Tràng | xã | xa_bat_trang | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 48 | 592 | Phường Từ Liêm | phường | phuong_tu_liem | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 49 | 598 | Phường Thượng Cát | phường | phuong_thuong_cat | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28
| 50 | 602 | Phường Đông Ngạc | phường | phuong_dong_ngac | 1 | Thành phố Hà Nội | 2026-10-07 13:35:28 | 2026-10-07 13:35:28

> Hiển thị 50/3,321 bản ghi. Toàn bộ dữ liệu nằm trong `data/wards.jsonl`.
