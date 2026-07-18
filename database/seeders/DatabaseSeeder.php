<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'sintiadewanggraini@gmail.com',
            ],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}