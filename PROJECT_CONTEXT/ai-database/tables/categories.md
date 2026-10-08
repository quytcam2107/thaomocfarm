# Bảng `categories`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-08T08:53:41+00:00

## Thông tin chung

- Số bản ghi: **5**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/categories.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `parent_id` | bigint unsigned | YES | *NULL* | - | - |
| `name` | varchar(255) | NO | *NULL* | - | - |
| `slug` | varchar(255) | NO | *NULL* | - | - |
| `icon` | varchar(255) | YES | *NULL* | - | - |
| `description` | text | YES | *NULL* | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `is_featured` | tinyint(1) | NO | 0 | - | - |
| `status` | enum('active','hidden') | NO | active | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| categories_is_featured_status_index |  | btree | `is_featured`, `status` |
| categories_parent_id_index |  | btree | `parent_id` |
| categories_parent_id_status_sort_order_index |  | btree | `parent_id`, `status`, `sort_order` |
| categories_slug_unique | ✔ | btree | `slug` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 5 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `4` (1), `5` (1)
- `name`: giá trị phổ biến: `Thảo mộc & Dược liệu` (1), `Thảo mộc ngâm rượu` (1), `Đặc sản Tây Bắc` (1), `Mật ong` (1), `Trà hoa Thảo Mộc` (1)
- `slug`: giá trị phổ biến: `dac-san-tay-bac` (1), `mat-ong` (1), `thao-moc` (1), `thao-moc-ngam-ruou` (1), `tra-hoa-thao-moc` (1)
- `icon`: giá trị phổ biến: `category-thao-moc-va-duoc-lieu.png` (1), `category-thao-moc-ngam-ruou.png` (1), `category-dac-san-tay-bac.png` (1), `mat-ong.png` (1), `category-tra-hoa-thao-moc.png` (1)
- `description`: giá trị phổ biến: `Đương quy, đẳng sâm, hà thủ ô, chè dây... thảo mộc quý vùng núi cao.` (1), `Thịt trâu, thịt lợn gác bếp chuẩn vị bản địa Tây Bắc.` (1), `Mắc khén, hạt dổi, thảo quả – linh hồn của món ăn vùng cao.` (1), `Mật ong rừng nguyên chất, sáp ong, phấn hoa.` (1), `NULL` (1)
- `sort_order`: min = 1, max = 5 — giá trị phổ biến: `1` (1), `2` (1), `3` (1), `5` (1), `4` (1)
- `is_featured`: min = 1, max = 1 — giá trị phổ biến: `1` (5)
- `status`: giá trị phổ biến: `active` (4), `hidden` (1)
- `created_at`: min = 2026-09-21 21:13:21, max = 2026-09-21 21:13:21 — giá trị phổ biến: `2026-09-21 21:13:21` (4), `NULL` (1)
- `updated_at`: min = 2026-09-21 21:13:21, max = 2026-09-21 21:13:21 — giá trị phổ biến: `2026-09-21 21:13:21` (4), `NULL` (1)

## Mẫu dữ liệu

| id | parent_id | name | slug | icon | description | sort_order | is_featured | status | created_at | updated_at
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | NULL | Thảo mộc & Dược liệu | thao-moc | category-thao-moc-va-duoc-lieu.png | Đương quy, đẳng sâm, hà thủ ô, chè dây... thảo mộc quý vùng núi cao. | 1 | 1 | active | 2026-09-21 21:13:21 | 2026-09-21 21:13:21
| 2 | NULL | Thảo mộc ngâm rượu | thao-moc-ngam-ruou | category-thao-moc-ngam-ruou.png | Thịt trâu, thịt lợn gác bếp chuẩn vị bản địa Tây Bắc. | 3 | 1 | active | 2026-09-21 21:13:21 | 2026-09-21 21:13:21
| 3 | NULL | Đặc sản Tây Bắc | dac-san-tay-bac | category-dac-san-tay-bac.png | Mắc khén, hạt dổi, thảo quả – linh hồn của món ăn vùng cao. | 5 | 1 | active | 2026-09-21 21:13:21 | 2026-09-21 21:13:21
| 4 | NULL | Mật ong | mat-ong | mat-ong.png | Mật ong rừng nguyên chất, sáp ong, phấn hoa. | 4 | 1 | hidden | 2026-09-21 21:13:21 | 2026-09-21 21:13:21
| 5 | NULL | Trà hoa Thảo Mộc | tra-hoa-thao-moc | category-tra-hoa-thao-moc.png | NULL | 2 | 1 | active | NULL | NULL
