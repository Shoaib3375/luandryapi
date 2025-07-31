<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class LogNotificationSent
{
    /**
     * Handle the event.
     *
     * @param  NotificationSent  $event
     * @return void
     */
    public function handle(NotificationSent $event): void
    {
        if ($event->channel === 'database') {
            Log::info('Notification stored in database', [
                'notifiable' => get_class($event->notifiable),
                'notification' => get_class($event->notification),
                'notifiable_id' => $event->notifiable->id
            ]);
        }
    }
}
