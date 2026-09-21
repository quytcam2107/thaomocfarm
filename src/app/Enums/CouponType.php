<?php
namespace App\Enums;

enum CouponType: string
{
    case FIXED = 'fixed';
    case PERCENT = 'percent';
    case SHIPPING = 'shipping';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Giảm cố định (₫)',
            self::PERCENT => 'Giảm phần trăm (%)',
            self::SHIPPING => 'Freeship',
        };
    }
}