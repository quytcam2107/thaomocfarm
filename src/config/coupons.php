<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cấu hình mã giảm giá (CouponService)
|--------------------------------------------------------------------------
|
| Tách từ CouponService: text thông báo validate + template mô tả tự sinh
| khi coupon trong DB trống description. Đổi câu chữ chỉ cần sửa file này.
|
*/

return [

    // Thông báo validate mã khi áp vào giỏ
    'messages' => [
        'invalid' => 'Mã giảm giá không tồn tại hoặc đã hết hiệu lực.',
        'disabled' => 'Mã giảm giá đã bị vô hiệu hóa.',
        'expired' => 'Mã giảm giá chưa bắt đầu hoặc đã hết hạn.',
        'not_eligible' => 'Giỏ hàng không có sản phẩm áp dụng được mã này.',
        'exhausted' => 'Mã giảm giá đã hết lượt sử dụng.',
        'applied' => 'Áp dụng mã :code thành công.',
    ],

    // Template mô tả tự sinh theo loại mã ({value}/{max} = số tiền đã format)
    'desc_templates' => [
        'fixed' => 'Giảm {value} cho đơn hàng áp dụng.',
        'percent' => 'Giảm {value}% đơn hàng',
        'shipping' => 'Miễn phí vận chuyển cho đơn hàng áp dụng.',
        'default' => 'Áp dụng cho đơn hàng đủ điều kiện.',
    ],

    // Nhãn hiển thị điều kiện tối thiểu / hạn dùng
    'labels' => [
        'min_order_prefix' => 'Đơn tối thiểu ',
        'no_min_cart' => 'Không có điều kiện tối thiểu',
        'no_min_public' => 'Không yêu cầu tối thiểu',
        'expiry_prefix' => 'HSD: ',
        'no_expiry' => 'Không giới hạn',
        'max_discount_suffix' => ', tối đa ',
    ],
];