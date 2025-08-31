<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@smshub.com'],
            [
                'name' => 'SMS Hub Admin',
                'email' => 'admin@smshub.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );
    }
}
