<?php
namespace App\Enums;

enum PromotionStatus: string
{
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case ENDED = 'ended';
    case CANCELLED = 'cancelled';
}