<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /** Key session lưu mã đang áp cho giỏ hàng. */
    public const SESSION_KEY = 'cart_coupon_code';

    /**
     * Lấy giá trị enum type an toàn dù model có cast hay không.
     */
    private function typeValue(Coupon $coupon): string
    {
        $type = $coupon->type;
        return $type instanceof CouponType ? $type->value : (string) $type;
    }

    /**
     * Lấy giá trị enum status an toàn.
     */
    private function statusValue(Coupon $coupon): string
    {
        $status = $coupon->status;
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }

    /**
     * Tìm mã theo code (không phân biệt hoa thường).
     */
    public function findByCode(string $code): ?Coupon
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        return Coupon::whereRaw('UPPER(code) = ?', [$code])->first();
    }

    /**
     * Kiểm tra cửa sổ thời gian hiệu lực.
     */
    private function isWithinTimeWindow(Coupon $coupon): bool
    {
        $now = now();
        if ($coupon->starts_at !== null && $now->lt($coupon->starts_at)) {
            return false;
        }
        if ($coupon->expires_at !== null && $now->gt($coupon->expires_at)) {
            return false;
        }
        return true;
    }

    /**
     * Tính subtotal của các item trong giỏ THUỘC phạm vi áp dụng của mã.
     */
    public function eligibleSubtotal(int $cartId, Coupon $coupon): int
    {
        $productIds = DB::table('coupon_products')->where('coupon_id', $coupon->id)->pluck('product_id')->all();
        $categoryIds = DB::table('coupon_categories')->where('coupon_id', $coupon->id)->pluck('category_id')->all();

        $query = CartItem::where('cart_items.cart_id', $cartId)
            ->join('product_variants', 'product_variants.id', '=', 'cart_items.product_variant_id');

        if ($productIds === [] && $categoryIds === []) {
            return (int) $query->sum(DB::raw('product_variants.price * cart_items.qty'));
        }

        if ($categoryIds !== []) {
            $query->join('products', 'products.id', '=', 'cart_items.product_id');
        }

        $query->where(function (Builder $q) use ($productIds, $categoryIds): void {
            if ($productIds !== []) {
                $q->orWhereIn('cart_items.product_id', $productIds);
            }
            if ($categoryIds !== []) {
                $q->orWhereIn('products.category_id', $categoryIds);
            }
        });

        return (int) $query->sum(DB::raw('product_variants.price * cart_items.qty'));
    }

    /**
     * Kiểm tra lượt sử dụng còn lại.
     */
    private function hasUsageRemaining(Coupon $coupon, ?int $userId): bool
    {
        if ($coupon->usage_limit !== null) {
            $used = DB::table('coupon_usages')->where('coupon_id', $coupon->id)->count();
            if ($used >= (int) $coupon->usage_limit) {
                return false;
            }
        }
        if ($userId !== null && (int) $coupon->per_user_limit > 0) {
            $usedByUser = DB::table('coupon_usages')
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->count();
            if ($usedByUser >= (int) $coupon->per_user_limit) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate mã cho giỏ hàng hiện tại.
     *
     * @return array{ok: bool, reason: string, eligible: int}
     */
    public function validateForCart(Coupon $coupon, int $cartId, ?int $userId): array
    {
        if ($this->statusValue($coupon) !== 'active') {
            return ['ok' => false, 'reason' => 'Mã giảm giá đã bị vô hiệu hóa.', 'eligible' => 0];
        }
        if (!$this->isWithinTimeWindow($coupon)) {
            return ['ok' => false, 'reason' => 'Mã giảm giá chưa bắt đầu hoặc đã hết hạn.', 'eligible' => 0];
        }
        $eligible = $this->eligibleSubtotal($cartId, $coupon);
        if ($eligible <= 0) {
            return ['ok' => false, 'reason' => 'Giỏ hàng không có sản phẩm áp dụng được mã này.', 'eligible' => 0];
        }
        if ($eligible < (int) $coupon->min_order_value) {
            return [
                'ok' => false,
                'reason' => 'Mã chỉ áp dụng cho đơn từ ' . format_vnd((int) $coupon->min_order_value) . ' (hiện tại ' . format_vnd($eligible) . ').',
                'eligible' => $eligible,
            ];
        }
        if (!$this->hasUsageRemaining($coupon, $userId)) {
            return ['ok' => false, 'reason' => 'Mã giảm giá đã hết lượt sử dụng.', 'eligible' => $eligible];
        }
        return ['ok' => true, 'reason' => '', 'eligible' => $eligible];
    }

    /**
     * Tính số tiền giảm theo loại mã.
     */
    public function calculateDiscount(Coupon $coupon, int $eligibleSubtotal, int $shippingFee): int
    {
        $type = $this->typeValue($coupon);
        $value = (int) $coupon->value;

        if ($eligibleSubtotal <= 0) {
            return 0;
        }
        return match ($type) {
            'fixed' => min($value, $eligibleSubtotal),
            'percent' => min(
                (int) floor($eligibleSubtotal * $value / 100),
                $coupon->max_discount !== null ? (int) $coupon->max_discount : PHP_INT_MAX,
                $eligibleSubtotal
            ),
            'shipping' => min($shippingFee, $value > 0 ? $value : $shippingFee),
            default => 0,
        };
    }

    /**
     * Áp mã vào giỏ (lưu session).
     *
     * @return array{success: bool, message: string, code: string}
     */
    public function applyToCart(string $code, int $cartId, ?int $userId): array
    {
        $coupon = $this->findByCode($code);
        if ($coupon === null) {
            return ['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã hết hiệu lực.', 'code' => ''];
        }
        $check = $this->validateForCart($coupon, $cartId, $userId);
        if (!$check['ok']) {
            return ['success' => false, 'message' => $check['reason'], 'code' => ''];
        }
        session([self::SESSION_KEY => strtoupper((string) $coupon->code)]);
        return [
            'success' => true,
            'message' => 'Áp dụng mã ' . $coupon->code . ' thành công.',
            'code' => (string) $coupon->code,
        ];
    }

    /**
     * Đọc mã đang áp trong session và re-validate. Hết hiệu lực => tự xoá.
     *
     * @return array{coupon: Coupon, code: string, type: string, eligible: int}|null
     */
    public function resolveAppliedCoupon(int $cartId, ?int $userId): ?array
    {
        $code = strtoupper(trim((string) session(self::SESSION_KEY, '')));
        if ($code === '') {
            return null;
        }
        $coupon = $this->findByCode($code);
        if ($coupon === null) {
            $this->removeAppliedCoupon();
            return null;
        }
        $check = $this->validateForCart($coupon, $cartId, $userId);
        if (!$check['ok']) {
            $this->removeAppliedCoupon();
            return null;
        }
        return [
            'coupon' => $coupon,
            'code' => $code,
            'type' => $this->typeValue($coupon),
            'eligible' => $check['eligible'],
        ];
    }

    /**
     * Gỡ mã khỏi session giỏ hàng.
     */
    public function removeAppliedCoupon(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Danh sách mã đang phát hành — map ra đúng 4 props mà component cpn dùng:
     *  - code:      string (mã uppercase)
     *  - desc:      string (mô tả, tự sinh ngắn gọn nếu DB trống)
     *  - minOrder:  string (đã format "Đơn tối thiểu xxx.000₫")
     *  - exp:       string (HSD dd/mm/yyyy hoặc "Không giới hạn")
     *  - applied:   bool  (đang là mã đang áp trong session — dùng để wrap highlight)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableCoupons(int $cartId, ?int $userId): array
    {
        $now = now();

        $coupons = Coupon::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->orderBy('min_order_value')
            ->orderBy('id')
            ->get();

        $sessionCode = strtoupper(trim((string) session(self::SESSION_KEY, '')));
        $out = [];

        foreach ($coupons as $coupon) {
            $type = $this->typeValue($coupon);
            $value = (int) $coupon->value;
            $min = (int) $coupon->min_order_value;
            $max = $coupon->max_discount !== null ? (int) $coupon->max_discount : null;
            $code = strtoupper((string) $coupon->code);

            // Mô tả hiển thị trong cpn__body. Ưu tiên description DB, fallback tự sinh.
            $dbDesc = trim((string) ($coupon->description ?? ''));
            if ($dbDesc !== '') {
                $desc = $dbDesc;
            } else {
                $desc = match ($type) {
                    'fixed' => 'Giảm ' . format_vnd($value) . ' cho đơn hàng áp dụng.',
                    'percent' => 'Giảm ' . $value . '% đơn hàng' . ($max !== null ? ', tối đa ' . format_vnd($max) : '') . '.',
                    'shipping' => 'Miễn phí vận chuyển cho đơn hàng áp dụng.',
                    default => 'Áp dụng cho đơn hàng đủ điều kiện.',
                };
            }

            $minOrder = $min > 0 ? 'Đơn tối thiểu ' . format_vnd($min) : 'Không có điều kiện tối thiểu';

            $exp = $coupon->expires_at !== null
                ? 'HSD: ' . $coupon->expires_at->format('d/m/Y')
                : 'Không giới hạn';

            $applied = $sessionCode !== '' && $sessionCode === $code;

            $out[] = [
                'code' => $code,
                'desc' => $desc,
                'minOrder' => $minOrder,
                'exp' => $exp,
                'applied' => $applied,
            ];
        }

        return $out;
    }

    /**
     * API cũ giữ nguyên chữ ký cho checkout.
     *
     * @return array{success: bool, message: string, discount: int, code: string}
     */
    public function applyCoupon(string $code, int $cartTotal, ?int $userId): array
    {
        $coupon = $this->findByCode($code);
        if ($coupon === null) {
            return ['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã hết hiệu lực.', 'discount' => 0, 'code' => ''];
        }
        if ($this->statusValue($coupon) !== 'active' || !$this->isWithinTimeWindow($coupon)) {
            return ['success' => false, 'message' => 'Mã giảm giá chưa bắt đầu hoặc đã hết hạn.', 'discount' => 0, 'code' => ''];
        }
        if ($cartTotal < (int) $coupon->min_order_value) {
            return [
                'success' => false,
                'message' => 'Mã chỉ áp dụng cho đơn từ ' . format_vnd((int) $coupon->min_order_value) . '.',
                'discount' => 0,
                'code' => '',
            ];
        }
        if (!$this->hasUsageRemaining($coupon, $userId)) {
            return ['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.', 'discount' => 0, 'code' => ''];
        }
        $discount = $this->calculateDiscount($coupon, $cartTotal, 0);
        return ['success' => true, 'message' => 'Áp dụng mã ' . $coupon->code . ' thành công.', 'discount' => $discount, 'code' => (string) $coupon->code];
    }
    /**
     * Ghi nhận lượt sử dụng coupon sau khi đặt hàng thành công.
     * Dùng cho checkout transaction.
     */
    public function recordUsage(int $couponId, ?int $userId, int $orderId, int $discountAmount): void
    {
        \Illuminate\Support\Facades\DB::table('coupon_usages')->insert([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}