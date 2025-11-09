<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing OAuth clients to only have client_credentials grant type
        // grant_types is stored as JSON text in the database
        DB::table('oauth_clients')->update([
            'grant_types' => json_encode(['client_credentials']),
        ]);
    }

    /**
     * Reverse the migrations.
     * 
     * Note: We cannot fully reverse this migration as we don't know
     * what the original grant_types were for each client.
     */
    public function down(): void
    {
        // This migration cannot be fully reversed
        // If needed, you would need to restore from a backup
    }

    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return config('passport.connection');
    }
};
