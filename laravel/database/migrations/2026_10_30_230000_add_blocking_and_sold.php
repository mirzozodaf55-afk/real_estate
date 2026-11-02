<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Блокировка пользователя администратором
        Schema::table('owners', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('role')->index();
            $table->text('block_reason')->nullable()->after('blocked_at');
        });

        // Владелец отметил «Продано» / «Сдано» — объявление уходит из поиска, но остаётся у владельца
        Schema::table('announs', function (Blueprint $table) {
            $table->timestamp('sold_at')->nullable()->after('moderated_by')->index();
        });
    }

    public function down(): void
    {
        Schema::table('announs', fn (Blueprint $table) => $table->dropColumn('sold_at'));
        Schema::table('owners', fn (Blueprint $table) => $table->dropColumn(['blocked_at', 'block_reason']));
    }
};
