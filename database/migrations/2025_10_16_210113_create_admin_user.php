<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@domain.uz',
            'password' => Hash::make('12345678'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove admin user
        User::where('email', 'admin@domain.uz')->delete();
    }
};
