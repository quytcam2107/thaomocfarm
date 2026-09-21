<?php
namespace App\Enums;

enum BannerPosition: string
{
    case HOME_HERO = 'home_hero';
    case HOME_MID = 'home_mid';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
}