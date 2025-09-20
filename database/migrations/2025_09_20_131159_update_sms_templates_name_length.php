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
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('name', 500)->change(); // Increase name length to 500 characters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('name', 255)->change(); // Revert to original length
        });
    }
};