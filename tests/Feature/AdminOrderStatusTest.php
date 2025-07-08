<?php

namespace Tests\Feature;

use App\Models\LaundryOrder;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminOrderStatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    public function test_admin_can_update_order_status()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 5.00]);

//        $order = LaundryOrder::create([
//            'user_id' => $user->id,
//            'service_id' => $service->id,
//            'quantity' => 3,
//            'total_price' => 15.00,
//            'status' => 'Pending',
//        ]);
        $order = \App\Models\LaundryOrder::factory()->create();

        $token = auth()->login($admin);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/orders/{$order->id}/status", ['status' => 'Processing']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('laundry_orders', [
            'id' => $order->id,
            'status' => 'Processing',
        ]);
    }
}
