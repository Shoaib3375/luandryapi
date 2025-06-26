<?php

namespace Database\Seeders;

use App\Models\LaundryOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaundryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LaundryOrder::create([
            'user_id' => 2,
            'service_id' => 1,
            'quantity' => 3,
            'total_price' => 6.00,
            'status' => 'Pending',
            'payment_status' => 'Unpaid',
        ]);

        LaundryOrder::create([
            'user_id' => 2,
            'service_id' => 2,
            'quantity' => 2.5,
            'total_price' => 3.75,
            'status' => 'Completed',
            'payment_status' => 'Paid',
        ]);
    }
}
