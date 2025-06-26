<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@laundry.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'John Customer',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
