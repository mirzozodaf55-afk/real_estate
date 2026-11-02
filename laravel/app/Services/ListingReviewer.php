<?php

namespace App\Services;

use App\Models\Announ;
use RuntimeException;

/**
 * ИИ-проверка текста и цены объявления для модератора: риск (low/medium/high), проблемы, причина отказа.
 * Результат хранится в announs.ai_review; общий риск объявления (с учётом фото) — announs.ai_risk.
 */
class ListingReviewer
{
    public const RISKS = ['high', 'medium', 'low']; // по убыванию — так же сортируется очередь

    /** Свежий результат (объявление не меняли после проверки) берём из базы, иначе спрашиваем ИИ */
    public static function review(Announ $announ, bool $force = false): array
    {
        if (!$force && $announ->ai_review && $announ->ai_review_at && (!$announ->updated_at || $announ->ai_review_at->gte($announ->updated_at))) {
            return $announ->ai_review + ['cached' => true];
        }

        $market = self::market($announ);
        $result = OpenAi::json([
            ['role' => 'system', 'content' => implode("\n", [
                'Ты помогаешь модератору сайта недвижимости в Таджикистане (цены в сомони) заранее найти проблемы в объявлении.',
                'Проверь: подозрительно низкую или высокую цену относительно рынка, телефоны/ссылки/мессенджеры в тексте (запрещены), признаки мошенничества (предоплата, «срочно, уезжаю»),',
                'несоответствия (комнат больше, чем позволяет площадь, этаж выше этажности), пустое или бессмысленное описание, оскорбления, рекламу.',
                'Фото ты не видишь — не делай выводов о них. Не придумывай проблем: если всё в порядке, так и скажи.',
                'suggested_reason — вежливая причина отказа для владельца (1–2 предложения) или пустая строка, если отказывать не за что.',
            ])],
            ['role' => 'user', 'content' => json_encode([
                'объявление' => array_filter([
                    'сделка' => $announ->deal_type, 'аренда' => $announ->rental_type, 'тип' => $announ->type_object,
                    'цена' => $announ->price, 'комнат' => $announ->quantity_room, 'площадь' => $announ->total_area ?: $announ->house_area,
                    'жилая' => $announ->living_area, 'кухня' => $announ->kitchen_area, 'этаж' => $announ->floor, 'этажность' => $announ->floor_in_house,
                    'год' => $announ->year_of_construction, 'ремонт' => $announ->repair, 'фото' => $announ->pictures()->count(),
                    'заголовок' => $announ->title, 'описание' => $announ->description,
                ], fn ($v) => $v !== null && $v !== ''),
                'рынок' => $market,
            ], JSON_UNESCAPED_UNICODE)],
        ], 'moderation_review', [
            'type' => 'object',
            'properties' => [
                'risk' => ['type' => 'string', 'enum' => self::RISKS],
                'summary' => ['type' => 'string'],
                'issues' => ['type' => 'array', 'items' => ['type' => 'string']],
                'suggested_reason' => ['type' => 'string'],
            ],
            'required' => ['risk', 'summary', 'issues', 'suggested_reason'],
            'additionalProperties' => false,
        ], ['temperature' => 0.2]);

        if (!in_array($result['risk'] ?? null, self::RISKS, true)) {
            throw new RuntimeException('Неожиданный ответ ИИ при проверке объявления');
        }
        $review = [
            'risk' => $result['risk'],
            'summary' => (string) $result['summary'],
            'issues' => array_values(array_map('strval', $result['issues'] ?? [])),
            'suggested_reason' => (string) $result['suggested_reason'],
            'market' => $market,
        ];

        $announ->forceFill(['ai_review' => $review, 'ai_review_at' => now()]);
        $announ->timestamps = false; // updated_at — время правки владельцем
        $announ->save();
        self::refreshRisk($announ);

        return $review + ['cached' => false];
    }

    /**
     * Общий риск = худшее из двух проверок: текст/цена (ai_review.risk) и фото (photo_check.verdict:
     * mismatch → high, suspicious → medium, ok → low). Нет ни одной проверки — null («не проверено»).
     */
    public static function refreshRisk(Announ $announ): void
    {
        $photo = ['mismatch' => 'high', 'suspicious' => 'medium', 'ok' => 'low'][$announ->photo_check['verdict'] ?? ''] ?? null;
        $text = $announ->ai_review['risk'] ?? null;
        $risk = collect(self::RISKS)->first(fn ($r) => $r === $photo || $r === $text);

        if ($announ->ai_risk !== $risk) {
            $announ->forceFill(['ai_risk' => $risk]);
            $announ->timestamps = false;
            $announ->save();
        }
    }

    /** С чем сравнивать цену: опубликованные объявления того же типа и комнатности */
    private static function market(Announ $announ): array
    {
        $similar = Announ::query()
            ->where('id', '!=', $announ->id)
            ->where('deal_type', $announ->deal_type)
            ->where('type_object', $announ->type_object)
            ->when($announ->deal_type === 'Аренда', fn ($q) => $q->where('rental_type', $announ->rental_type))
            ->when($announ->quantity_room, fn ($q) => $q->where('quantity_room', $announ->quantity_room))
            ->pluck('price')->filter()->sort()->values();

        return $similar->count() >= 2 ? [
            'похожих_объявлений' => $similar->count(),
            'медиана' => $similar[intdiv($similar->count(), 2)],
            'минимум' => $similar->first(),
            'максимум' => $similar->last(),
        ] : ['похожих_объявлений' => $similar->count(), 'примечание' => 'мало данных для сравнения цены'];
    }
}
