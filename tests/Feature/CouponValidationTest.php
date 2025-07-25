<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_validate_valid_coupon()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a valid coupon
        $coupon = Coupon::create([
            'code' => 'SUMMER15',
            'discount_percent' => 10,
            'expires_at' => now()->addDays(10),
        ]);

        // Login as user
        $token = auth()->login($user);

        // Send request to validate coupon
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/coupons/validate', [
                'code' => 'SUMMER15',
            ]);

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'code',
                    'discount_percent',
                    'expires_at',
                    'valid'
                ],
                'message'
            ])
            ->assertJson([
                'data' => [
                    'code' => 'SUMMER15',
                    'discount_percent' => 10,
                    'valid' => true
                ],
                'message' => 'Coupon is valid.'
            ]);
    }

    public function test_user_cannot_validate_expired_coupon()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expired coupon
        $coupon = Coupon::create([
            'code' => 'SUMMER15',
            'discount_percent' => 10,
            'expires_at' => now()->subDays(1),
        ]);

        // Login as user
        $token = auth()->login($user);

        // Send request to validate coupon
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/coupons/validate', [
                'code' => 'SUMMER15',
            ]);

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Invalid coupon',
                'errors' => 'The coupon code is invalid or has expired.'
            ]);
    }

    public function test_user_cannot_validate_nonexistent_coupon()
    {
        // Create a user
        $user = User::factory()->create();

        // Login as user
        $token = auth()->login($user);

        // Send request to validate coupon
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/coupons/validate', [
                'code' => 'SUMMER15',
            ]);

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Invalid coupon',
                'errors' => 'The coupon code is invalid or has expired.'
            ]);
    }
}
