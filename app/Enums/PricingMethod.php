<?php

namespace App\Enums;

enum PricingMethod: string
{
    case PER_KG = 'per_kg';
    case PER_ITEM = 'per_item';
    case FLAT_RATE = 'flat_rate';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}