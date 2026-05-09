<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE messages
            MODIFY COLUMN status
            ENUM('queued','processing','sent','delivered','failed')
            NOT NULL DEFAULT 'queued'
        ");
    }

    public function down(): void
    {
        DB::table('messages')
            ->where('status', 'processing')
            ->update(['status' => 'queued']);

        DB::statement("
            ALTER TABLE messages
            MODIFY COLUMN status
            ENUM('queued','sent','delivered','failed')
            NOT NULL DEFAULT 'queued'
        ");
    }
};
