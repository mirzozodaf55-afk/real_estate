<?php

namespace Database\Seeders;

use App\Models\Announ;
use App\Models\Owner;
use Illuminate\Database\Seeder;

/**
 * Демо-данные для этапа 1 модерации: очередь с разным риском ИИ, пара дубликатов по тексту, объявление «на доработке».
 * Все демо-записи принадлежат владельцам с почтой demo-…@example.invalid — убрать их: php artisan moderation:demo-clear
 *
 * Запуск: php artisan db:seed --class=ModerationDemoSeeder
 */
class ModerationDemoSeeder extends Seeder
{
    public const EMAIL_PATTERN = 'demo-%@example.invalid';

    public function run(): void
    {
        $alice = Owner::firstOrCreate(['email' => 'demo-alice@example.invalid'], ['uniq_id' => '90000001', 'name' => 'Демо', 'last_name' => 'Владелец']);
        $bob = Owner::firstOrCreate(['email' => 'demo-bob@example.invalid'], ['uniq_id' => '90000002', 'name' => 'Демо', 'last_name' => 'Второй']);

        $dupText = 'Сдаётся двухкомнатная квартира в Сино, 54 м², третий этаж девятиэтажного дома. Свежий ремонт, мебель и техника, рядом школа и остановка.';

        $rows = [
            // [владелец, статус, риск, заголовок, цена, описание, ИИ-сводка, проблемы, комментарий модератора]
            [$alice, 'pending', 'high', 'Сдаётся 2-комн., предоплата за 3 месяца', 900, 'Срочно, уезжаю. Предоплата за 3 месяца, звоните +992 90 000 00 00.',
                'Цена намного ниже рынка, в тексте телефон и просьба о предоплате.', ['Цена в 4 раза ниже медианы', 'Телефон в описании', 'Предоплата — частый признак мошенничества'], null],
            [$alice, 'pending', 'medium', 'Квартира в Сино, 54 м²', 3600, $dupText,
                'Похоже на правду, но в описании мало деталей о доме.', ['Не указан год постройки'], null],
            [$bob, 'pending', 'medium', 'Квартира в Сино, 54 м², ремонт', 3500, $dupText,
                'Текст почти совпадает с другим объявлением.', ['Возможный дубликат'], null],
            [$bob, 'pending', 'low', 'Студия в центре Душанбе', 2800, 'Уютная студия 28 м² на 5 этаже, новый дом, есть лифт и парковка.',
                'Проблем не найдено.', [], null],
            [$alice, 'pending', null, 'Комната для студента', 800, 'Комната в трёхкомнатной квартире, рядом университет.', null, [], null],
            [$bob, 'needs_changes', 'low', 'Дом с участком в Вахдате', 450000, 'Дом 120 м², участок 8 соток.',
                'Проблем не найдено.', [], 'Добавьте фото дома снаружи и участка.'],
        ];

        foreach ($rows as $i => [$owner, $status, $risk, $title, $price, $description, $summary, $issues, $comment]) {
            $announ = new Announ();
            $announ->forceFill([
                'owner_id' => $owner->id,
                'deal_type' => $price > 100000 ? 'Продажа' : 'Аренда',
                'rental_type' => $price > 100000 ? null : 'Длительно',
                'type_real_estate' => 'Жилая',
                'type_object' => str_contains($title, 'Дом') ? 'Дом/Дача' : (str_contains($title, 'Комната') ? 'Комната' : 'Квартира'),
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'moderation_status' => $status,
                'moderation_reason' => $comment,
                'ai_risk' => $risk,
                'ai_review' => $summary ? ['risk' => $risk, 'summary' => $summary, 'issues' => $issues, 'suggested_reason' => '', 'market' => ['похожих_объявлений' => 0]] : null,
                'ai_review_at' => $summary ? now() : null,
                'created_at' => now()->subHours(6 - $i),
                'updated_at' => now()->subHours(6 - $i),
            ]);
            $announ->timestamps = false;
            $announ->save();
        }

        $this->command?->info('Демо-объявлений добавлено: ' . count($rows) . '. Убрать: php artisan moderation:demo-clear');
    }
}
