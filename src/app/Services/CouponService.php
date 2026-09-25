<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Carbon;

class CouponService
{
    /**
     * Kiểm tra và áp dụng mã giảm giá.
     *
     * @param string $code Mã coupon
     * @param int $cartTotal Tổng tiền giỏ hàng (integer VND)
     * @param int|null $userId ID user (null nếu là guest)
     * @return array|null Trả về array ['coupon_id', 'discount_amount', 'coupon_code'] hoặc null nếu không hợp lệ
     */
    public function applyCoupon(string $code, int $cartTotal, ?int $userId = null): ?array
    {
        $coupon = Coupon::where('code', $code)
            ->where('status', 'active')
            ->first();

        if (!$coupon) {
            return null;
        }

        // Kiểm tra thời gian hiệu lực
        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return null;
        }
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return null;
        }

        // Kiểm tra tổng lượt sử dụng
        if ($coupon->usage_limit !== null) {
            $totalUsage = CouponUsage::where('coupon_id', $coupon->id)->count();
            if ($totalUsage >= $coupon->usage_limit) {
                return null;
            }
        }

        // Kiểm tra lượt sử dụng per user
        if ($userId && $coupon->per_user_limit !== null) {
            $userUsage = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->count();
            if ($userUsage >= $coupon->per_user_limit) {
                return null;
            }
        }

        // Kiểm tra điều kiện đơn hàng tối thiểu
        if ($coupon->min_order_value && $cartTotal < $coupon->min_order_value) {
            return null;
        }

        // Tính toán số tiền giảm
        $discountAmount = 0;
        $couponType = CouponType::from($coupon->type);

        switch ($couponType) {
            case CouponType::FIXED:
                $discountAmount = (int) $coupon->value;
                break;

            case CouponType::PERCENT:
                $discountAmount = (int) round($cartTotal * ((int) $coupon->value / 100));
                // Áp dụng max_discount nếu có
                if ($coupon->max_discount !== null && $discountAmount > $coupon->max_discount) {
                    $discountAmount = (int) $coupon->max_discount;
                }
                break;

            case CouponType::SHIPPING:
                // Giảm phí ship sẽ được xử lý riêng ở CheckoutService
                $discountAmount = (int) $coupon->value;
                break;
        }

        // Đảm bảo không giảm quá tổng tiền
        if ($discountAmount > $cartTotal) {
            $discountAmount = $cartTotal;
        }

        return [
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
            'coupon_code' => $coupon->code,
            'coupon_type' => $coupon->type,
        ];
    }

    /**
     * Ghi nhận việc sử dụng coupon (gọi sau khi tạo đơn thành công).
     */
    public function recordUsage(int $couponId, ?int $userId, int $orderId, int $discountAmount): void
    {
        CouponUsage::create([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);
    }
}