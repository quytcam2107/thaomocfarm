<?php
namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case PACKING = 'packing';
    case SHIPPING = 'shipping';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNING = 'returning';

    /** Nhãn tiếng Việt hiển thị cho user */
    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Mới tiếp nhận',
            self::CONFIRMED => 'Đã xác nhận',
            self::PACKING => 'Đang đóng gói',
            self::SHIPPING => 'Đang giao hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::CANCELLED => 'Đã hủy',
            self::RETURNING => 'Đang đổi/trả',
        };
    }

    /** Trạng thái được phép chuyển đổi tiếp theo (quy trình đơn giản) */
    public function next(): array
    {
        return match ($this) {
            self::NEW => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::PACKING, self::CANCELLED],
            self::PACKING => [self::SHIPPING, self::CANCELLED],
            self::SHIPPING => [self::DELIVERED, self::RETURNING, self::CANCELLED],
            self::DELIVERED => [self::RETURNING],
            self::CANCELLED, self::RETURNING => [],
        };
    }
}