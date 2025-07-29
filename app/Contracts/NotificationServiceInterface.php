<?php

namespace App\Contracts;

use App\Models\LaundryOrder;

interface NotificationServiceInterface
{
    public function notifyOrderStatusUpdate(LaundryOrder $order): void;
}