<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Pending';
    case PROCESSING = 'Processing';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}