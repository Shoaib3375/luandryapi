<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::create([
            'code' => 'WELCOME10',
            'discount_percent' => 10,
            'expires_at' => now()->addDays(30),
        ]);

        Coupon::create([
            'code' => 'SUMMER15',
            'discount_percent' => 15,
            'expires_at' => now()->addDays(10),
        ]);
    }
}
