<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Результат проверки фото ИИ: соответствуют ли снимки объявлению (см. App\Services\PhotoChecker)
        Schema::table('announs', function (Blueprint $table) {
            $table->json('photo_check')->nullable()->after('sold_at');
            $table->timestamp('photo_check_at')->nullable()->after('photo_check');
        });
    }

    public function down(): void
    {
        Schema::table('announs', fn (Blueprint $table) => $table->dropColumn(['photo_check', 'photo_check_at']));
    }
};
