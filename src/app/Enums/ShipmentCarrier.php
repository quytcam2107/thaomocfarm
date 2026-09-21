<?php
namespace App\Enums;

enum ShipmentCarrier: string
{
    case INTERNAL = 'internal';
    case GHN = 'ghn';
    case GHTK = 'ghtk';
    case VTP = 'vtp';
}