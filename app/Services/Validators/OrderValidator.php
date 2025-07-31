<?php

namespace App\Services\Validators;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\LaundryOrder;
use App\Models\User;

class OrderValidator
{
    /**
     * @throws OrderException
     */
    public function validateStatusUpdate(LaundryOrder $order, OrderStatus $newStatus, User $user): void
    {
        if (!$user->is_admin) {
            throw OrderException::unauthorized();
        }
        if ($order->status === 'Completed' && $newStatus === OrderStatus::PENDING) {
            throw OrderException::invalidStatusTransition();
        }
    }

    /**
     * @throws OrderException
     */
    public function validateCancellation(LaundryOrder $order): void
    {
        if (OrderStatus::from($order->status) !== OrderStatus::PENDING) {
            throw OrderException::cannotCancelNonPendingOrder();
        }
    }

    /**
     * @throws OrderException
     */
    public function validateUpdate(LaundryOrder $order): void
    {
        if (OrderStatus::from($order->status) !== OrderStatus::PENDING) {
            throw OrderException::cannotUpdateNonPendingOrder();
        }
    }

    /**
     * @throws OrderException
     */
    public function validateOwnership(LaundryOrder $order, User $user): void
    {
        if (!$user->is_admin && $order->user_id !== $user->id) {
            throw OrderException::unauthorized();
        }
    }
}
