<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Статус «прочитано» для сообщений чата (строка message_users = сообщение от owner_id к friend_id).
     */
    public function up(): void
    {
        Schema::table('message_users', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('friend_id');
            $table->index(['friend_id', 'read_at']);
        });

        // Всю уже существующую переписку считаем прочитанной, чтобы у всех не загорелись сотни "новых"
        DB::table('message_users')->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('message_users', function (Blueprint $table) {
            $table->dropIndex(['friend_id', 'read_at']);
            $table->dropColumn('read_at');
        });
    }
};
