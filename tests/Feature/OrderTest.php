<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_place_order()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'price' => 10.00,
        ]);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'services' => [
                    [
                        'service_id' => $service->id,
                        'quantity' => 2,
                    ]
                ],
            ]);
//        dump($response->json());

        $response->assertStatus(201);
        $response->assertJsonFragment(['total_price' => 20.00]);
    }
}
