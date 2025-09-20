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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Template name
            $table->text('content'); // Template content
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('provider_template_id')->nullable(); // ID from provider (Eskiz)
            $table->json('variables')->nullable(); // Template variables like {name}, {code}
            $table->text('rejection_reason')->nullable(); // Reason for rejection
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->index(['provider_id', 'status']);
            $table->unique(['provider_id', 'provider_template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
