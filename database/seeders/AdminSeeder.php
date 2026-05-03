<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password123'),
            ]
        );

        $user->assignRole('admin');

        $user = User::firstOrCreate(
            ['email' => 'agent@gmail.com'],
            [
                'name'     => 'Agent',
                'password' => Hash::make('password123'),
            ]
        );  
        $user->assignRole('agent');
    }
}