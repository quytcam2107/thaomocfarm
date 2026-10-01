<?php

declare(strict_types=1);

namespace App\Services\Coupon;

use App\Enums\CouponType;
use App\Models\Coupon;

/*
 * CouponTexts — toàn bộ câu chữ fix cứng của CouponService (tách ra để
 * service gọn; nội dung y hệt bản gốc, đổi text chỉ cần sửa config/coupons.php).
 */
class CouponTexts
{
    /** Text thông báo validate/áp mã. */
    public static function message(string $key, array $replace = []): string
    {
        $text = (string) config('coupons.messages.' . $key, '');

        foreach ($replace as $k => $v) {
            $text = str_replace($k, $v, $text);
        }

        return $text;
    }

    /**
     * Mô tả tự sinh theo loại mã khi DB trống description.
     * Giữ đúng công thức cũ: percent có nối ", tối đa {max}" khi max_discount != null.
     */
    public static function autoDescription(Coupon $coupon): string
    {
        $type = $coupon->type instanceof CouponType ? $coupon->type->value : (string) $coupon->type;

        $templates = (array) config('coupons.desc_templates');
        $labels = (array) config('coupons.labels');
        $tpl = (string) ($templates[$type] ?? $templates['default']);

        if ($type === 'percent') {
            $value = (int) $coupon->value;
            if ($coupon->max_discount !== null) {
                $tpl .= ((string) ($labels['max_discount_suffix'] ?? ', tối đa '))
                    . format_vnd((int) $coupon->max_discount);
            }
            return str_replace('{value}', (string) $value, $tpl) . '.';
        }

        if ($type === 'fixed') {
            return str_replace('{value}', format_vnd((int) $coupon->value), $tpl);
        }

        return $tpl;
    }

    /** Dòng "Đơn tối thiểu xxx₫" (hoặc nhãn không điều kiện). */
    public static function minOrderLabel(int $min, string $fallbackKey): string
    {
        $labels = (array) config('coupons.labels');

        return $min > 0
            ? ((string) ($labels['min_order_prefix'] ?? 'Đơn tối thiểu ')) . format_vnd($min)
            : (string) ($labels[$fallbackKey] ?? '');
    }

    /** Dòng hạn dùng: "HSD: dd/mm/yyyy" hoặc "Không giới hạn". */
    public static function expiryLabel(?\DateTimeInterface $expiresAt): string
    {
        $labels = (array) config('coupons.labels');

        return $expiresAt !== null
            ? ((string) ($labels['expiry_prefix'] ?? 'HSD: ')) . $expiresAt->format('d/m/Y')
            : (string) ($labels['no_expiry'] ?? 'Không giới hạn');
    }
}