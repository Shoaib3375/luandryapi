<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class OrderStatusUpdated implements ShouldBroadcast
{
    use InteractsWithSockets;

    public $message;
    public $userId;

    public function __construct($message, $userId)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }
}

?>
