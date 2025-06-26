<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create([
            'name' => 'T-Shirt Wash',
            'category' => 'Clothing',
            'price' => 2.00,
            'pricing_method' => 'fixed',
        ]);

        Service::create([
            'name' => 'Curtain Cleaning',
            'category' => 'Home',
            'price' => 1.5,
            'pricing_method' => 'weight',
        ]);
    }
}
