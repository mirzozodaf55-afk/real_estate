<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Одна отметка «в избранном» на пару пользователь+объявление: без индекса двойной клик
        // успевал вставить две строки (дубль в избранном, завышенный счётчик).
        // На всякий случай убираем уже существующие дубли, оставляя самую раннюю отметку.
        DB::statement('DELETE l1 FROM likes l1 JOIN likes l2 ON l1.user_id = l2.user_id AND l1.announ_id = l2.announ_id AND l1.id > l2.id');

        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['user_id', 'announ_id']);
            $table->index('announ_id'); // счётчик «в избранном» считается по объявлению
        });
    }

    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'announ_id']);
            $table->dropIndex(['announ_id']);
        });
    }
};
