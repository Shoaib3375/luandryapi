<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Foundation\Auth\User;

class SendOrderStatusNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event)
    {
        $user = User::find($event->userId);
        $user->notify(new OrderStatusChangedNotification($event->message));
    }
}
