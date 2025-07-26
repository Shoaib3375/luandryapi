<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID = 'Paid';
    case UNPAID = 'Unpaid';
    case REFUNDED = 'Refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}