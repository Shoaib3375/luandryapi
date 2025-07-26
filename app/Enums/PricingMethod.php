<?php

namespace App\Enums;

enum PricingMethod: string
{
    case FIXED = 'fixed';
    case WEIGHT = 'weight';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}