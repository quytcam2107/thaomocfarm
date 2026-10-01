<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\CartItem;
use App\Services\Coupon\CouponTexts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * CouponService — vòng đời mã giảm giá trong giỏ (find/validate/apply/resolve).
 * Toàn bộ câu chữ fix cứng đã tách sang App\Services\Coupon\CouponTexts
 * + config/coupons.php; public API và logic giữ nguyên 100%.
 */
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

        // Không ràng buộc phạm vi => cả giỏ đều đủ điều kiện
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
            return ['ok' => false, 'reason' => CouponTexts::message('disabled'), 'eligible' => 0];
        }
        if (!$this->isWithinTimeWindow($coupon)) {
            return ['ok' => false, 'reason' => CouponTexts::message('expired'), 'eligible' => 0];
        }
        $eligible = $this->eligibleSubtotal($cartId, $coupon);
        if ($eligible <= 0) {
            return ['ok' => false, 'reason' => CouponTexts::message('not_eligible'), 'eligible' => 0];
        }
        if ($eligible < (int) $coupon->min_order_value) {
            return [
                'ok' => false,
                'reason' => 'Mã chỉ áp dụng cho đơn từ ' . format_vnd((int) $coupon->min_order_value) . ' (hiện tại ' . format_vnd($eligible) . ').',
                'eligible' => $eligible,
            ];
        }
        if (!$this->hasUsageRemaining($coupon, $userId)) {
            return ['ok' => false, 'reason' => CouponTexts::message('exhausted'), 'eligible' => $eligible];
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
            return ['success' => false, 'message' => CouponTexts::message('invalid'), 'code' => ''];
        }
        $check = $this->validateForCart($coupon, $cartId, $userId);
        if (!$check['ok']) {
            return ['success' => false, 'message' => $check['reason'], 'code' => ''];
        }
        session([self::SESSION_KEY => strtoupper((string) $coupon->code)]);
        return [
            'success' => true,
            'message' => CouponTexts::message('applied', [':code' => (string) $coupon->code]),
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
     * Danh sách mã đang phát hành — map ra đúng props mà component cpn dùng:
     *  code / desc / minOrder / exp / applied.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableCoupons(int $cartId, ?int $userId): array
    {
        $coupons = $this->activeCouponsQuery()->get();

        $sessionCode = strtoupper(trim((string) session(self::SESSION_KEY, '')));
        $out = [];

        foreach ($coupons as $coupon) {
            // Mô tả hiển thị trong cpn__body. Ưu tiên description DB, fallback tự sinh.
            $dbDesc = trim((string) ($coupon->description ?? ''));
            $desc = $dbDesc !== '' ? $dbDesc : CouponTexts::autoDescription($coupon);

            $out[] = [
                'code' => strtoupper((string) $coupon->code),
                'desc' => $desc,
                'minOrder' => CouponTexts::minOrderLabel((int) $coupon->min_order_value, 'no_min_cart'),
                'exp' => CouponTexts::expiryLabel($coupon->expires_at),
                'applied' => $sessionCode !== '' && $sessionCode === strtoupper((string) $coupon->code),
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
            return ['success' => false, 'message' => CouponTexts::message('invalid'), 'discount' => 0, 'code' => ''];
        }
        if ($this->statusValue($coupon) !== 'active' || !$this->isWithinTimeWindow($coupon)) {
            return ['success' => false, 'message' => CouponTexts::message('expired'), 'discount' => 0, 'code' => ''];
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
            return ['success' => false, 'message' => CouponTexts::message('exhausted'), 'discount' => 0, 'code' => ''];
        }
        $discount = $this->calculateDiscount($coupon, $cartTotal, 0);
        return ['success' => true, 'message' => CouponTexts::message('applied', [':code' => (string) $coupon->code]), 'discount' => $discount, 'code' => (string) $coupon->code];
    }

    /**
     * Ghi nhận lượt sử dụng coupon sau khi đặt hàng thành công.
     * Dùng cho checkout transaction.
     */
    public function recordUsage(int $couponId, ?int $userId, int $orderId, int $discountAmount): void
    {
        DB::table('coupon_usages')->insert([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Danh sách mã giảm giá công khai cho block trang chủ (không ngữ cảnh giỏ).
     * - Chỉ lấy mã status=active và nằm trong cửa sổ thời gian
     * - Trả mảng thuần (string) — an toàn cho cache driver file (bẫy serialize)
     *
     * @return array<int, array{code: string, desc: string, minOrder: string, exp: string}>
     */
    public function getPublicCoupons(int $limit = 8): array
    {
        $coupons = $this->activeCouponsQuery()
            ->limit($limit)
            ->get(['id', 'code', 'type', 'value', 'min_order_value', 'max_discount', 'description', 'expires_at']);

        $out = [];

        foreach ($coupons as $coupon) {
            // Mô tả: ưu tiên description trong DB, thiếu thì tự sinh ngắn gọn
            $dbDesc = trim((string) ($coupon->description ?? ''));
            $desc = $dbDesc !== '' ? $dbDesc : CouponTexts::autoDescription($coupon);

            $out[] = [
                'code' => strtoupper((string) $coupon->code),
                'desc' => $desc,
                'minOrder' => CouponTexts::minOrderLabel((int) $coupon->min_order_value, 'no_min_public'),
                'exp' => CouponTexts::expiryLabel($coupon->expires_at),
            ];
        }

        return $out;
    }

    /**
     * Query chung "đang phát hành": active + trong cửa sổ thời gian,
     * sắp xếp ổn định (tách để getAvailableCoupons/getPublicCoupons dùng lại).
     */
    private function activeCouponsQuery(): Builder
    {
        $now = now();

        return Coupon::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->orderBy('min_order_value')
            ->orderBy('id');
    }
}