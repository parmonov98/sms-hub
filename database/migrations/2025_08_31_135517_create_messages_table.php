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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider_message_id')->nullable(); // External provider message ID
            $table->string('to'); // Recipient phone number
            $table->string('from')->nullable(); // Sender ID
            $table->text('text'); // Message content
            $table->integer('parts')->default(1); // Number of SMS parts
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('price_decimal', 10, 4)->nullable(); // Cost in decimal
            $table->string('currency', 3)->default('USD');
            $table->string('idempotency_key')->unique(); // Prevent duplicates
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
