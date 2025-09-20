<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old provider_credentials table
        Schema::dropIfExists('provider_credentials');
        
        // Create the new provider_tokens table
        Schema::create('provider_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('token_type')->default('access'); // access, refresh, etc.
            $table->text('token_value'); // The actual token
            $table->timestamp('expires_at')->nullable(); // Token expiration
            $table->json('metadata')->nullable(); // Additional token metadata
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['provider_id', 'token_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new provider_tokens table
        Schema::dropIfExists('provider_tokens');
        
        // Recreate the old provider_credentials table
        Schema::create('provider_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->text('credentials'); // Encrypted credentials
            $table->json('settings')->nullable(); // Additional settings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['project_id', 'provider_id']);
        });
    }
};
