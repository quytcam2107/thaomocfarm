<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cấu hình trang chủ (HomeService)
|--------------------------------------------------------------------------
|
| Tách từ HomeService: các ngưỡng/số lượng fix cứng của block flash sale.
| Sửa ngưỡng không cần đụng code service.
|
*/

return [

    // Số lượng item tối đa mỗi block
    'limits' => [
        'featured_categories' => 8,
        'coupons' => 8,
        'best_sellers' => 4,
        'herbal_tea' => 8,
        'flash_sale' => 8,
    ],

    // TTL cache (giây) từng block — flash ngắn hơn vì deal đổi thường xuyên
    'ttl' => [
        'featured_categories' => 600,
        'coupons' => 600,
        'best_sellers' => 600,
        'herbal_tea' => 600,
        'flash_sale' => 300,
    ],

    // Ngưỡng text "kích thích mua hàng" trên card deal flash sale
    'flash_hooks' => [
        'urgent_percent' => 70,  // % đã bán -> "Sắp cháy hàng"
        'urgent_slots' => 10,  // slot còn lại -> "Sắp cháy hàng"
        'deep_discount_percent' => 30, // % giảm -> "Giảm giá sâu"
        'hot_sold_count' => 100, // lượt bán -> "Bán chạy"
    ],
];