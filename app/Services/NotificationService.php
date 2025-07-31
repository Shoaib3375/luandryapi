<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Events\OrderStatusUpdated as OrderStatusUpdatedEvent;
use App\Models\LaundryOrder;
use App\Notifications\OrderStatusUpdated;

class NotificationService implements NotificationServiceInterface
{
    public function notifyOrderStatusUpdate(LaundryOrder $order): void
    {
        try {
            $order->load('user');
            
            // Only send notification if order has a registered user
            if ($order->user && method_exists($order->user, 'notify')) {
                $order->user->notify(new OrderStatusUpdated($order));
                \Log::info('Order status notification sent', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'status' => $order->status
                ]);
            } else {
                \Log::info('Skipping notification for guest order', [
                    'order_id' => $order->id,
                    'guest_email' => $order->guest_email
                ]);
            }
            
            // Only fire event for registered users
            if ($order->user_id) {
                event(new OrderStatusUpdatedEvent(
                    "Your order #{$order->id} status updated to {$order->status}",
                    $order->user_id
                ));
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}