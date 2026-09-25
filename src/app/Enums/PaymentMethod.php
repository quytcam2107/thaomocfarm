<?php
namespace App\Enums;

enum PaymentMethod: string
{
    case COD = 'cod';
    // case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::COD => 'Thanh toán khi nhận hàng (COD)',
            // self::BANK_TRANSFER => 'Chuyển khoản ngân hàng',
        };
    }
}