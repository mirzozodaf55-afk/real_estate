<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Риск по мнению ИИ: проверка текста/цены (ai_review) + проверка фото (photo_check) → ai_risk для сортировки очереди.
        // Статус «на доработку» (needs_changes) живёт в том же moderation_status, комментарий — в moderation_reason.
        Schema::table('announs', function (Blueprint $table) {
            $table->json('ai_review')->nullable()->after('photo_check_at');
            $table->timestamp('ai_review_at')->nullable()->after('ai_review');
            $table->string('ai_risk', 8)->nullable()->after('ai_review_at'); // high | medium | low | null — не проверено
            $table->index(['moderation_status', 'ai_risk']);
        });

        // Перцептивный хеш фото (pHash, 64 бита в hex) — для поиска одинаковых снимков в разных объявлениях
        Schema::table('pictures', function (Blueprint $table) {
            $table->char('phash', 16)->nullable()->after('rotation')->index();
        });

        // Готовые причины отказа (редактирует админ). text — что увидит владелец
        Schema::create('rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('text', 500);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // стартовый список из задания; «Другое» — не строка справочника, а свободный текст модератора
        $reasons = [
            ['Фото не соответствуют объекту', 'Фотографии не соответствуют объекту в объявлении. Загрузите настоящие фото этого объекта.'],
            ['Нереальная цена', 'Цена сильно отличается от рыночной. Укажите настоящую цену объекта.'],
            ['Контакты в описании', 'В тексте или на фото указаны телефоны, ссылки или мессенджеры. Уберите их — контакты видны в вашем профиле.'],
            ['Дубликат объявления', 'Такое объявление уже размещено на сайте. Повторно размещать одно и то же объявление нельзя.'],
        ];
        foreach ($reasons as $i => [$title, $text]) {
            DB::table('rejection_reasons')->insert(['title' => $title, 'text' => $text, 'sort' => $i, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rejection_reasons');
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropIndex(['phash']);
            $table->dropColumn('phash');
        });
        Schema::table('announs', function (Blueprint $table) {
            $table->dropIndex(['moderation_status', 'ai_risk']);
            $table->dropColumn(['ai_review', 'ai_review_at', 'ai_risk']);
        });
    }
};
