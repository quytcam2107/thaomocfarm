<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình đơn hàng
    |--------------------------------------------------------------------------
    |
    | Tiền tố mã đơn hàng và các định dạng liên quan.
    | Format: {prefix}-{YYYYMMDD}-{5 số ngẫu nhiên}
    |
    */
    'order' => [
        'prefix' => env('ORDER_PREFIX', 'TMX'),
        'random_length' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Rate Limiting (giới hạn tần suất)
    |--------------------------------------------------------------------------
    |
    | Số lần cho phép trong một phút cho các hành động nhạy cảm.
    |
    */
    'rate_limits' => [
        'login' => env('RATE_LIMIT_LOGIN', 5),
        'checkout' => env('RATE_LIMIT_CHECKOUT', 3),
        'apply_coupon' => env('RATE_LIMIT_APPLY_COUPON', 10),
        'search' => env('RATE_LIMIT_SEARCH', 30),
        'send_otp' => env('RATE_LIMIT_SEND_OTP', 3),
        // NEW: tra cứu đơn hàng bằng SĐT (chống dò số hàng loạt theo IP)
        'order_lookup' => env('RATE_LIMIT_ORDER_LOOKUP', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Cache TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Thời gian sống của cache tính bằng giây cho từng nhóm dữ liệu.
    | Lưu ý: Sử dụng helper remember_group() để quản lý version cache.
    |
    */
    'cache' => [
        'home' => [
            'featured_categories' => 600, // 10 phút
            'flash_sale' => 300,          // 5 phút
            'best_sellers' => 600,        // 10 phút
            'banners' => 3600,            // 1 giờ
        ],
        'catalog' => [
            'category_tree' => 3600,      // 1 giờ
            'product_detail' => 300,      // 5 phút
            'flash_sale' => 300,
        ],
        'content' => [
            'posts' => 1800,              // 30 phút
            'testimonials' => 3600,       // 1 giờ
        ],
        'settings' => 3600,               // 1 giờ
        'flash_live' => [
            'ttl' => 180,                 // 3 phút — đúng chu kỳ flash-live.js
            'stale_ttl' => 86400,         // bản fallback 1 ngày cho nhánh STALE
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Vận chuyển
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'default_fee' => env('SHIPPING_DEFAULT_FEE', 30000),
        'fast_fee' => env('SHIPPING_FAST_FEE', 30000),
        'free_threshold' => env('SHIPPING_FREE_THRESHOLD', 300000),
        'internal_carrier_name' => 'Giao hàng nội bộ Thảo Mộc Farm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Upload Ảnh
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size_kb' => env('UPLOAD_MAX_SIZE_KB', 2048), // 2MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'thumbnails' => [
            'small' => 300,
            'medium' => 600,
            'large' => 1200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Review / Đánh giá
    |--------------------------------------------------------------------------
    */
    'review' => [
        'require_verified_purchase' => true, // Chỉ cho phép đánh giá nếu đã mua hàng
        'auto_approve' => false,             // Cần admin duyệt
    ],
];