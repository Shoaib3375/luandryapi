<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_place_order()
    {
        $service = Service::factory()->create(['price' => 10.00]);

        $response = $this->postJson('/api/guest/orders', [
            'service_id' => $service->id,
            'quantity' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '1234567890',
            'guest_address' => '123 Main St, City',
            'note' => 'Please handle with care'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'service_id',
                        'quantity',
                        'total_price',
                        'guest_name',
                        'guest_email',
                        'guest_phone',
                        'guest_address',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('laundry_orders', [
            'service_id' => $service->id,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'user_id' => null
        ]);
    }

    public function test_guest_order_requires_all_fields()
    {
        $service = Service::factory()->create();

        $response = $this->postJson('/api/guest/orders', [
            'service_id' => $service->id,
            'quantity' => 2,
            // Missing required guest fields
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['guest_name', 'guest_email', 'guest_phone', 'guest_address']);
    }
}