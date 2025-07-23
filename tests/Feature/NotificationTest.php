<?php

namespace Tests\Feature;

use App\Models\LaundryOrder;
use App\Models\Service;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_stored_in_database_when_order_status_changes()
    {
        // Disable actual notifications during test
        Notification::fake();
        
        // Create admin, user, service and order
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 5.00]);
        $order = LaundryOrder::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'Pending'
        ]);

        // Login as admin
        $token = auth()->login($admin);

        // Update order status
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/orders/{$order->id}/status", ['status' => 'Processing']);

        // Assert response is successful
        $response->assertStatus(200);
        
        // Assert notification was sent to the right user
        Notification::assertSentTo(
            $user,
            OrderStatusUpdated::class,
            function ($notification, $channels) use ($order) {
                // Assert notification was sent to database channel
                return in_array('database', $channels);
            }
        );
    }
}