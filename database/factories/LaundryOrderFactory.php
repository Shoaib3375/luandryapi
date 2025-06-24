<?php

namespace Database\Factories;

use App\Models\LaundryOrder;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaundryOrder>
 */
class LaundryOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = LaundryOrder::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 5); // e.g., 2.50 kg
        $service = Service::factory()->create([
            'price' => 10.00,
        ]);

        return [
            'user_id' => User::factory(),
            'service_id' => $service->id,
            'quantity' => $quantity,
            'total_price' => $service->price * $quantity,
            'status' => $this->faker->randomElement(['Pending', 'Processing', 'Completed', 'Cancelled']),
        ];
    }
}
