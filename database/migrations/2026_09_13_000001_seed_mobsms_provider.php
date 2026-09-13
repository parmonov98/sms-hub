<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Provider;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the MobSMS provider disabled by default so the currently
        // active provider (Eskiz) keeps working until an admin switches to
        // MobSMS from the admin panel. Enabling one provider automatically
        // disables the others (see Provider::booted()).
        $provider = Provider::updateOrCreate(
            ['display_name' => 'mobsms'],
            [
                'display_name' => 'mobsms',
                'description' => 'MobSMS Cloud - Android gateway (no sender ID / template moderation)',
                'capabilities' => [
                    'dlr' => true,
                    'unicode' => true,
                    'concat' => true,
                    'flash' => false,
                ],
                'is_enabled' => false,
                'priority' => 2,
            ]
        );

        Log::info('MobSMS provider seeded successfully', [
            'provider_id' => $provider->id,
            'display_name' => $provider->display_name,
        ]);

        // Note: the MobSMS API key is read from config('services.mobsms.api_key')
        // (env MOBSMS_API_KEY); no provider token is stored in the database.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Provider::where('display_name', 'mobsms')->delete();
        Log::info('MobSMS provider removed successfully');
    }
};
