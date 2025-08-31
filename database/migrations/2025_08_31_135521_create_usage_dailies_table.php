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
        Schema::create('usage_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('messages')->default(0);
            $table->integer('parts')->default(0);
            $table->decimal('cost_decimal', 10, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            
            $table->unique(['project_id', 'date']);
            $table->index(['project_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_dailies');
    }
};
