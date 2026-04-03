<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add Admin User
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'hashed_password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Add Staff User
        User::firstOrCreate(
            ['username' => 'staff'],
            [
                'hashed_password' => \Illuminate\Support\Facades\Hash::make('staff123'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );

        $this->call(FloorPlanSeeder::class);
    }
}
