<?php
namespace App\Enums;

enum OrderPaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ thanh toán',
            self::PAID => 'Đã thanh toán',
            self::REFUNDED => 'Đã hoàn tiền',
        };
    }
}