<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Provider;
use App\Models\ProviderToken;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create or update Eskiz provider
        $provider = Provider::updateOrCreate(
            ['display_name' => 'eskiz'],
            [
                'display_name' => 'eskiz',
                'description' => 'Eskiz SMS Gateway - Uzbekistan\'s leading SMS service provider',
                'capabilities' => [
                    'dlr' => true,           // Delivery reports
                    'unicode' => true,       // Unicode support
                    'concat' => true,        // Long message concatenation
                    'flash' => false,        // Flash SMS support
                    'scheduled' => true,     // Scheduled SMS
                    'bulk' => true,          // Bulk SMS
                    'templates' => true,     // Template support
                ],
                'is_enabled' => true,
                'priority' => 1,
            ]
        );

        Log::info('Eskiz provider seeded successfully', [
            'provider_id' => $provider->id,
            'display_name' => $provider->display_name,
        ]);

        // Note: Provider tokens will be created automatically when the system
        // authenticates with Eskiz using ESKIZ_EMAIL and ESKIZ_PASSWORD from .env
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Eskiz provider and its tokens
        $provider = Provider::where('display_name', 'eskiz')->first();

        if ($provider) {
            // Delete associated tokens
            $provider->tokens()->delete();

            // Delete the provider
            $provider->delete();

            Log::info('Eskiz provider removed successfully');
        }
    }
};
