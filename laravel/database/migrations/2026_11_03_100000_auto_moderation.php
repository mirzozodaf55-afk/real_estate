<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // решения автомодерации пишутся в журнал без модератора (App\Services\AutoModerator)
        DB::statement('ALTER TABLE moderation_logs MODIFY moderator_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::table('moderation_logs')->whereNull('moderator_id')->delete();
        DB::statement('ALTER TABLE moderation_logs MODIFY moderator_id BIGINT UNSIGNED NOT NULL');
    }
};
