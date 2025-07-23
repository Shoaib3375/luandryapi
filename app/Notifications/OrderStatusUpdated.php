<?php

namespace App\Notifications;

use App\Models\LaundryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

class OrderStatusUpdated extends Notification
{
    // Removed ShouldQueue to ensure immediate processing

    protected $order;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($orderOrMessage)
    {
        if ($orderOrMessage instanceof LaundryOrder) {
            $this->order = $orderOrMessage;
            $this->message = "Your order id {$this->order->id} status updated to {$this->order->status}";
            
            // Log order status change
            Log::info('Order status changed', [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'user_id' => $this->order->user_id
            ]);
        } else {
            $this->message = $orderOrMessage;
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'message' => $this->message,
        ];

        if ($this->order) {
            $data['order_id'] = $this->order->id;
            $data['status'] = $this->order->status;
        }

        return $data;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
