<?php
namespace App\Enums;

enum PromotionType: string
{
    case FLASH_SALE = 'flash_sale';
    case CAMPAIGN = 'campaign';
}