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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('capabilities'); // e.g., dlr, unicode, concat
            $table->json('default_config')->nullable(); // Default configuration
            $table->boolean('is_enabled')->default(true);
            $table->integer('priority')->default(0); // For failover order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
