<?php

namespace Database\Seeders;

use App\Models\LaundryOrder;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaundryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Washing',
            'price_per_unit' => 2.00,
        ]);

        LaundryOrder::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'quantity' => 3,
            'total_price' => 6.00,
            'status' => 'Pending',
            'payment_status' => 'Unpaid',
        ]);
    }
}
