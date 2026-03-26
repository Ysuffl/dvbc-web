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
        // FastAPI compatible hashed password for 'admin'
        // Using $2y$ format which is compatible with both Laravel and Passlib/FastAPI
        User::create([
            'username' => 'admin',
            'hashed_password' => \Illuminate\Support\Facades\Hash::make('admin'),
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
    }
}
