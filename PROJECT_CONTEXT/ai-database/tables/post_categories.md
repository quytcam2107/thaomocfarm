# Bảng `post_categories`

> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.
> Bản cập nhật lúc: 2026-10-07T13:36:43+00:00

## Thông tin chung

- Số bản ghi: **3**
- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `data/post_categories.jsonl`

## Cấu trúc (columns)

| Cột | Kiểu | Null | Mặc định | Extra | Comment |
|---|---|---|---|---|---|
| `id` | bigint unsigned | NO | *NULL* | - | - |
| `name` | varchar(255) | NO | *NULL* | - | - |
| `slug` | varchar(255) | NO | *NULL* | - | - |
| `sort_order` | smallint unsigned | NO | 0 | - | - |
| `status` | enum('active','hidden') | NO | active | - | - |
| `created_at` | timestamp | YES | *NULL* | - | - |
| `updated_at` | timestamp | YES | *NULL* | - | - |

## Indexes

| Tên | Unique | Kiểu | Cột |
|---|---|---|---|
| post_categories_slug_unique | ✔ | btree | `slug` |
| primary | ✔ | btree | `id` |

## Thống kê dữ liệu

- `id`: min = 1, max = 3 — giá trị phổ biến: `1` (1), `2` (1), `3` (1)
- `name`: giá trị phổ biến: `Vào bếp cùng Mộc Xanh` (1), `Pha trà & thưởng trà` (1), `Bảo quản & sức khỏe` (1)
- `slug`: giá trị phổ biến: `bao-quan-suc-khoe` (1), `pha-tra` (1), `vao-bep` (1)
- `sort_order`: min = 1, max = 3 — giá trị phổ biến: `1` (1), `2` (1), `3` (1)
- `status`: giá trị phổ biến: `active` (3)
- `created_at`: min = 2026-10-02 16:54:28, max = 2026-10-02 16:54:28 — giá trị phổ biến: `2026-10-02 16:54:28` (3)
- `updated_at`: min = 2026-10-02 16:54:28, max = 2026-10-02 16:54:28 — giá trị phổ biến: `2026-10-02 16:54:28` (3)

## Mẫu dữ liệu

| id | name | slug | sort_order | status | created_at | updated_at
|---|---|---|---|---|---|---|
| 1 | Vào bếp cùng Mộc Xanh | vao-bep | 1 | active | 2026-10-02 16:54:28 | 2026-10-02 16:54:28
| 2 | Pha trà & thưởng trà | pha-tra | 2 | active | 2026-10-02 16:54:28 | 2026-10-02 16:54:28
| 3 | Bảo quản & sức khỏe | bao-quan-suc-khoe | 3 | active | 2026-10-02 16:54:28 | 2026-10-02 16:54:28
