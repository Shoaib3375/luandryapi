<?php

namespace App\Services;

use App\Events\OrderStatusUpdated as OrderStatusUpdatedEvent;
use App\Models\LaundryOrder;
use App\Notifications\OrderStatusUpdated;

class NotificationService
{
    public function notifyOrderStatusUpdate(LaundryOrder $order): void
    {
        try {
            $order->load('user');
            
            if ($order->user && method_exists($order->user, 'notify')) {
                $order->user->notify(new OrderStatusUpdated($order));
            }
            
            event(new OrderStatusUpdatedEvent(
                "Your order #{$order->id} status updated to {$order->status}",
                $order->user_id
            ));
        } catch (\Throwable $e) {
            // Log the error but don't fail the operation
            \Log::error('Failed to send notification: ' . $e->getMessage());
        }
    }
}