<?php

declare(strict_types=1);

namespace App\Services\Cart;

/*
 * CartTotals — pure-function tính tiền giỏ hàng, tách từ
 * CartService::getCartSummary() để service chính gọn.
 * GIỮ NGUYÊN 100% công thức gốc:
 *   goodsDiscount trừ trước -> ship tính trên subtotal đã trừ ->
 *   mã shipping miễn toàn bộ ship -> total = discounted + fee - shipDiscount.
 */
class CartTotals
{
    /**
     * @param array{code: string, type: string, eligible: int}|null $applied
     *        mã đã resolve (chưa có key discount) — null khi không áp mã.
     * @param callable(int):int $shippingFeeFor hàm tính phí ship theo subtotal đã trừ giảm giá
     * @return array{discounted_subtotal: int, shipping_fee: int, shipping_discount: int, discount: int, total: int}
     */
    public static function compute(
        int $subtotal,
        ?array $applied,
        int $goodsDiscount,
        callable $shippingFeeFor
    ): array {
        $discountedSubtotal = max(0, $subtotal - $goodsDiscount);
        $shippingFee = $shippingFeeFor($discountedSubtotal);

        // Mã type=shipping => miễn toàn bộ phí ship vừa tính
        $shippingDiscount = ($applied !== null && $applied['type'] === 'shipping')
            ? $shippingFee
            : 0;

        $discount = $goodsDiscount + $shippingDiscount;
        $total = $discountedSubtotal + $shippingFee - $shippingDiscount;

        return [
            'discounted_subtotal' => $discountedSubtotal,
            'shipping_fee' => $shippingFee,
            'shipping_discount' => $shippingDiscount,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}