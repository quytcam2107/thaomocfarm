<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cấu hình Catalog (danh mục / sản phẩm / tìm kiếm)
|--------------------------------------------------------------------------
|
| Tách từ CatalogService: các dữ liệu fix cứng (SEO text, khoảng giá,
| sắp xếp, phân trang). Sửa nội dung SEO chỉ cần edit file này hoặc
| data/catalog-seo.json — không đụng vào code service.
|
*/

return [
    // Số sản phẩm mỗi trang ở trang danh mục
    'per_page' => 12,

    // Số sản phẩm mỗi trang ở trang "Tất cả sản phẩm" và "Tìm kiếm"
    'all_products_per_page' => 8,

    // Các lựa chọn sắp xếp hợp lệ (key => label hiển thị)
    'sort_options' => [
        'bestsell' => 'Bán chạy nhất',
        'newest' => 'Mới nhất',
        'price_asc' => 'Giá thấp → cao',
        'price_desc' => 'Giá cao → thấp',
    ],

    // Khoảng giá lọc được (VND; max = null nghĩa là không chặn trên)
    'price_ranges' => [
        ['key' => '0-100', 'min' => 0, 'max' => 100000],
        ['key' => '100-250', 'min' => 100000, 'max' => 250000],
        ['key' => '250-', 'min' => 250000, 'max' => null],
    ],

    // Ngưỡng rating cho phép lọc ở trang danh mục
    'rating_filters' => [3, 4, 5],

    // Nội dung SEO text cố định — đọc từ JSON để service gọn nhẹ
    'seo_file' => base_path('data/catalog-seo.json'),
];