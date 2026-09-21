<?php
namespace App\Enums;

enum StockMovementType: string
{
    case IMPORT = 'import';
    case EXPORT = 'export';
    case ADJUST = 'adjust';
    case ORDER_RESERVE = 'order_reserve';
    case ORDER_RELEASE = 'order_release';
}