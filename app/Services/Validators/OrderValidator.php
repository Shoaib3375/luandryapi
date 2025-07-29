<?php

namespace App\Services\Validators;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\LaundryOrder;
use App\Models\User;

class OrderValidator
{
    public function validateStatusUpdate(LaundryOrder $order, OrderStatus $newStatus, User $user): void
    {
        if (!$user->is_admin) {
            throw OrderException::unauthorized();
        }
    }

    public function validateCancellation(LaundryOrder $order): void
    {
        if (OrderStatus::from($order->status) !== OrderStatus::PENDING) {
            throw OrderException::cannotCancelNonPendingOrder();
        }
    }

    public function validateUpdate(LaundryOrder $order): void
    {
        if (OrderStatus::from($order->status) !== OrderStatus::PENDING) {
            throw OrderException::cannotUpdateNonPendingOrder();
        }
    }

    public function validateOwnership(LaundryOrder $order, User $user): void
    {
        if (!$user->is_admin && $order->user_id !== $user->id) {
            throw OrderException::unauthorized();
        }
    }
}