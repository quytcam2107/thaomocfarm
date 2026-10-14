# Bảng `reviews`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-14T07:25:06+00:00

## Thông tin chung

- Số bản ghi: **12**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/reviews.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `product_id` | bigint unsigned | NO | *NULL* | - | - |
| `user_id` | bigint unsigned | YES | *NULL* | - | - |
| `ip_address` | varchar(45) | YES | *NULL* | - | - |
| `customer_name` | varchar(100) | YES | *NULL* | - | - |
| `customer_phone` | varchar(20) | YES | *NULL* | - | - |
| `order_id` | bigint unsigned | YES | *NULL* | - | - |
| `rating` | tinyint unsigned | NO | *NULL* | - | - |
| `content` | text | NO | *NULL* | - | - |
| `images` | json | YES | *NULL* | - | - |
| `is_verified` | tinyint(1) | NO | 0 | - | - |
| `admin_reply` | text | YES | *NULL* | - | - |
| `status` | enum('pending','approved','hidden') | NO | pending | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| primary | ✔ | btree | `id` |
| reviews_order_id_index |  | btree | `order_id` |
| reviews_order_id_product_id_unique | ✔ | btree | `order_id`, `product_id` |
| reviews_product_id_index |  | btree | `product_id` |
| reviews_product_id_rating_index |  | btree | `product_id`, `rating` |
| reviews_product_id_status_created_at_index |  | btree | `product_id`, `status`, `created_at` |
| reviews_user_id_index |  | btree | `user_id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 12 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1), `6` (1), `7` (1), `8` (1), `9` (1), `10` (1), `11` (1), `12` (1)
- `product_id`: min = 1, max = 1 — giá trị phổ biến: `1` (12)
- `user_id`: min = 1, max = 1 — giá trị phổ biến: `NULL` (11), `1` (1)
- `ip_address`: giá trị phổ biến: `127.0.0.1` (1), `::1` (1), `113.23.45.101` (1), `113.23.45.102` (1), `113.23.45.103` (1), `113.23.45.104` (1), `113.23.45.105` (1), `113.23.45.106` (1), `113.23.45.107` (1), `113.23.45.108` (1), `113.23.45.109` (1), `113.23.45.110` (1)
- `customer_name`: giá trị phổ biến: `Nguyen Thu` (1), `Hoàng Chấn` (1), `Trần Minh Hải` (1), `Nguyễn Thị Lan` (1), `Lê Văn Cường` (1), `Phạm Thu Hà` (1), `Võ Quốc Bảo` (1), `Đặng Thị Mai` (1), `Bùi Đức Toàn` (1), `Hoàng Thị Nhung` (1), `Ngô Văn Sơn` (1), `Lưu Thanh Trúc` (1)
- `customer_phone`: giá trị phổ biến: `NULL` (1), `0352806324` (1), `0912446127` (1), `0987654321` (1), `0905112233` (1), `0936778899` (1), `0913445566` (1), `0972334455` (1), `0903556677` (1), `0918223344` (1), `0968112233` (1), `0939887766` (1)
- `rating`: min = 4, max = 5 — giá trị phổ biến: `5` (11), `4` (1)
- `content`: giá trị phổ biến: `Hương vị rất thơm ngon, đóng gói cẩn thận. Tôi sẽ mua lại lần nữa.` (1), `Rất tốt, sản phẩm chuẩn Tây Bắc` (1), `Củ tam thất khô chuẩn, mặt cắt xám ngọc đúng như mô tả, tán bột uống rất dễ. Trừ 1 sao vì giao hàng hơi lâu so với hẹn (4 ngày). Nói chung vẫn sẽ mua lại.` (1), `Củ già 3-4 năm, cầm nặng tay, gõ nghe tiếng đanh. Thái lát hầm gà ác nước rất ngọt, hậu ngọt sâu ở cổ họng. Đóng gói túi zip kèm gói hút ẩm kỹ càng.` (1), `Mua về tán bột uống mỗi sáng 3g pha mật ong, người khỏe hơn rõ, ngủ sâu giấc. Shop hỗ trợ tán bột miễn phí nhanh gọn. Rất hài lòng.` (1), `Chị đồng nghiệp giới thiệu, đặt thử 500g. Củ đều nhau, không thấy vụn trộn bột lạ, hãm nước uống vị đắng nhẹ rồi ngọt hậu. Sẽ mua thêm làm quà biếu.` (1), `Giá cao hơn ngoài chợ nhưng chất lượng thì khỏi bàn: không mùi hắc sulphur, ruột xám xanh đúng chuẩn tam thất bắc. Có tem truy xuất nguồn gốc, yên tâm.` (1), `Sau sinh 6 tháng mình dùng theo hướng dẫn của shop, bổ máu, da dẻ hồng hào hơn. Tư vấn nhiệt tình qua hotline, gửi cả cách dùng chi tiết.` (1), `Mua 1kg loại 24-26 củ/kg ngâm rượu hạ thổ. Củ to đẹp đồng đều, không sâu mọt. Giao hàng đóng thùng carton chống sốc cẩn thận.` (1), `Nhà có người tai biến nên tôi mua về cho uống cùng thuốc theo chỉ định bác sĩ. Hàng chuẩn, không lẫn tam thất nam, vị đắng đặc trưng rõ rệt.` (1), `Lần thứ 3 đặt hàng. Bột tán mịn, hũ thủy tinh kín, để ngăn mát dùng dần. Ship nhanh, được freeship vì đơn trên 300k. Điểm 5 sao xứng đáng.` (1), `Thái lát ngâm mật ong 25 ngày đã dùng được, vị dễ uống, cả nhà đều thích. Đóng gói có túi hút ẩm và hướng dẫn bảo quản in rõ ràng.` (1)
- `is_verified`: min = 1, max = 1 — giá trị phổ biến: `1` (12)
- `status`: giá trị phổ biến: `approved` (12)
- `created_at`: min = 2026-08-21 10:09:02, max = 2026-10-13 10:51:21 — giá trị phổ biến: `2026-08-21 10:09:02` (1), `2026-08-21 10:38:47` (1), `2026-08-22 15:59:30` (1), `2026-09-14 13:49:49` (1), `2026-09-14 17:05:21` (1), `2026-09-20 15:39:45` (1), `2026-09-27 08:56:54` (1), `2026-10-05 19:12:11` (1), `2026-10-06 07:27:50` (1), `2026-10-07 20:27:45` (1), `2026-10-08 02:37:45` (1), `2026-10-13 10:51:21` (1)
- `updated_at`: min = 2026-08-21 10:09:02, max = 2026-10-13 10:51:21 — giá trị phổ biến: `2026-10-05 19:12:11` (1), `2026-10-06 07:27:50` (1), `2026-08-21 10:09:02` (1), `2026-10-07 20:27:45` (1), `2026-08-21 10:38:47` (1), `2026-10-08 02:37:45` (1), `2026-08-22 15:59:30` (1), `2026-10-13 10:51:21` (1), `2026-09-14 17:05:21` (1), `2026-09-20 15:39:45` (1), `2026-09-14 13:49:49` (1), `2026-09-27 08:56:54` (1)

## Mẫu dữ liệu

| id | product_id | user_id | ip_address | customer_name | customer_phone | order_id | rating | content | images | is_verified | admin_reply | status | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | 127.0.0.1 | Nguyen Thu | NULL | NULL | 5 | Hương vị rất thơm ngon, đóng gói cẩn thận. Tôi sẽ mua lại lần nữa. | NULL | 1 | NULL | approved | 2026-10-05 19:12:11 | 2026-10-05 19:12:11
| 2 | 1 | NULL | ::1 | Hoàng Chấn | 0352806324 | NULL | 5 | Rất tốt, sản phẩm chuẩn Tây Bắc | NULL | 1 | NULL | approved | 2026-10-06 07:27:50 | 2026-10-06 07:27:50
| 3 | 1 | NULL | 113.23.45.101 | Trần Minh Hải | 0912446127 | NULL | 4 | Củ tam thất khô chuẩn, mặt cắt xám ngọc đúng như mô tả, tán bột uống rất dễ. Trừ 1 sao vì giao hàng hơi lâu so với hẹn (4 ngày). Nói chung vẫn sẽ mua lại. | NULL | 1 | NULL | approved | 2026-08-21 10:09:02 | 2026-08-21 10:09:02
| 4 | 1 | NULL | 113.23.45.102 | Nguyễn Thị Lan | 0987654321 | NULL | 5 | Củ già 3-4 năm, cầm nặng tay, gõ nghe tiếng đanh. Thái lát hầm gà ác nước rất ngọt, hậu ngọt sâu ở cổ họng. Đóng gói túi zip kèm gói hút ẩm kỹ càng. | NULL | 1 | NULL | approved | 2026-10-07 20:27:45 | 2026-10-07 20:27:45
| 5 | 1 | NULL | 113.23.45.103 | Lê Văn Cường | 0905112233 | NULL | 5 | Mua về tán bột uống mỗi sáng 3g pha mật ong, người khỏe hơn rõ, ngủ sâu giấc. Shop hỗ trợ tán bột miễn phí nhanh gọn. Rất hài lòng. | NULL | 1 | NULL | approved | 2026-08-21 10:38:47 | 2026-08-21 10:38:47
| 6 | 1 | NULL | 113.23.45.104 | Phạm Thu Hà | 0936778899 | NULL | 5 | Chị đồng nghiệp giới thiệu, đặt thử 500g. Củ đều nhau, không thấy vụn trộn bột lạ, hãm nước uống vị đắng nhẹ rồi ngọt hậu. Sẽ mua thêm làm quà biếu. | NULL | 1 | NULL | approved | 2026-10-08 02:37:45 | 2026-10-08 02:37:45
| 7 | 1 | NULL | 113.23.45.105 | Võ Quốc Bảo | 0913445566 | NULL | 5 | Giá cao hơn ngoài chợ nhưng chất lượng thì khỏi bàn: không mùi hắc sulphur, ruột xám xanh đúng chuẩn tam thất bắc. Có tem truy xuất nguồn gốc, yên tâm. | NULL | 1 | NULL | approved | 2026-08-22 15:59:30 | 2026-08-22 15:59:30
| 8 | 1 | NULL | 113.23.45.106 | Đặng Thị Mai | 0972334455 | NULL | 5 | Sau sinh 6 tháng mình dùng theo hướng dẫn của shop, bổ máu, da dẻ hồng hào hơn. Tư vấn nhiệt tình qua hotline, gửi cả cách dùng chi tiết. | NULL | 1 | NULL | approved | 2026-10-13 10:51:21 | 2026-10-13 10:51:21
| 9 | 1 | NULL | 113.23.45.107 | Bùi Đức Toàn | 0903556677 | NULL | 5 | Mua 1kg loại 24-26 củ/kg ngâm rượu hạ thổ. Củ to đẹp đồng đều, không sâu mọt. Giao hàng đóng thùng carton chống sốc cẩn thận. | NULL | 1 | NULL | approved | 2026-09-14 17:05:21 | 2026-09-14 17:05:21
| 10 | 1 | NULL | 113.23.45.108 | Hoàng Thị Nhung | 0918223344 | NULL | 5 | Nhà có người tai biến nên tôi mua về cho uống cùng thuốc theo chỉ định bác sĩ. Hàng chuẩn, không lẫn tam thất nam, vị đắng đặc trưng rõ rệt. | NULL | 1 | NULL | approved | 2026-09-20 15:39:45 | 2026-09-20 15:39:45
| 11 | 1 | NULL | 113.23.45.109 | Ngô Văn Sơn | 0968112233 | NULL | 5 | Lần thứ 3 đặt hàng. Bột tán mịn, hũ thủy tinh kín, để ngăn mát dùng dần. Ship nhanh, được freeship vì đơn trên 300k. Điểm 5 sao xứng đáng. | NULL | 1 | NULL | approved | 2026-09-14 13:49:49 | 2026-09-14 13:49:49
| 12 | 1 | NULL | 113.23.45.110 | Lưu Thanh Trúc | 0939887766 | NULL | 5 | Thái lát ngâm mật ong 25 ngày đã dùng được, vị dễ uống, cả nhà đều thích. Đóng gói có túi hút ẩm và hướng dẫn bảo quản in rõ ràng. | NULL | 1 | NULL | approved | 2026-09-27 08:56:54 | 2026-09-27 08:56:54
