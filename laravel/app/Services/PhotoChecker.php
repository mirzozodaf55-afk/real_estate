<?php

namespace App\Services;

use App\Events\NotifyMe as NotifyMeEvent;
use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\Owner;
use RuntimeException;

/**
 * ИИ смотрит фото объявления и сравнивает с его данными (машина вместо квартиры, скриншоты, реклама…).
 * Результат сохраняется в announs.photo_check; если фото подозрительные — уведомляем всех модераторов и админов.
 * Нужна модель, которая видит картинки: OPENAI_VISION_MODEL (пусто — проверка выключена).
 */
class PhotoChecker
{
    // qwen/qwen3.8-27b на Groq принимает не больше 3 картинок за запрос (иначе HTTP 400 «Too many images»)
    private const MAX_PHOTOS = 3;
    public const OK = 'ok';
    public const SUSPICIOUS = 'suspicious';
    public const MISMATCH = 'mismatch';

    public static function enabled(): bool
    {
        return OpenAi::enabled() && (bool) config('services.openai.vision_model');
    }

    public static function check(Announ $announ): ?array
    {
        $pictures = $announ->pictures()->limit(self::MAX_PHOTOS)->get(['id', 'pictures']);
        $images = [];
        foreach ($pictures as $i => $picture) {
            // small (700×400): деталей хватает, а запрос остаётся лёгким
            $path = public_path('small/' . basename($picture->pictures));
            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                $images[] = ['type' => 'text', 'text' => 'Фото ' . ($i + 1) . ':'];
                $images[] = ['type' => 'image_url', 'image_url' => ['url' => "data:$mime;base64," . base64_encode(file_get_contents($path))]];
            }
        }
        if (!$images) {
            return null;
        }

        $facts = array_filter([
            'сделка' => $announ->deal_type === 'Аренда' && $announ->rental_type === 'Посуточно' ? 'Посуточно' : $announ->deal_type,
            'тип' => $announ->type_object,
            'категория' => $announ->type_real_estate,
            'комнат' => $announ->quantity_room,
            'площадь' => $announ->total_area ?: $announ->house_area,
            'ремонт' => $announ->repair,
            'заголовок' => $announ->title,
            'описание' => $announ->description ? mb_strimwidth($announ->description, 0, 400, '…') : null,
        ], fn ($v) => $v !== null && $v !== '');

        $result = OpenAi::json([
            ['role' => 'system', 'content' => implode("\n", [
                'Ты помогаешь модератору сайта недвижимости проверить, подходят ли фото к объявлению.',
                'Нормально: комнаты, кухня, санузел, балкон, дом снаружи, подъезд, двор, вид из окна, участок, планировка, офис/склад для коммерческой недвижимости. Ремонт или мебель на фото могут отличаться от описания — это не причина для тревоги.',
                'Подозрительно (suspicious): фото очень плохого качества или непонятно что; надписи с телефонами/ссылками поверх фото; явно чужое фото из интернета (водяной знак другого сайта).',
                'Не соответствует (mismatch): на фото не недвижимость — машина, животное, еда, селфи/люди крупным планом, скриншот экрана, документ, мем, реклама; или явно другой тип объекта (офисный центр для объявления о комнате).',
                'verdict: ok — все фото в порядке; suspicious — есть сомнения; mismatch — хотя бы одно фото явно не про это объявление.',
                'Отвечай по-русски. what — что на фото, коротко (до 10 слов). summary — одно предложение для модератора.',
            ])],
            ['role' => 'user', 'content' => array_merge(
                [['type' => 'text', 'text' => 'Объявление: ' . json_encode($facts, JSON_UNESCAPED_UNICODE)]],
                $images,
            )],
        ], 'photo_check', [
            'type' => 'object',
            'properties' => [
                'verdict' => ['type' => 'string', 'enum' => [self::OK, self::SUSPICIOUS, self::MISMATCH]],
                'summary' => ['type' => 'string'],
                'photos' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'index' => ['type' => 'integer'],
                        'ok' => ['type' => 'boolean'],
                        'what' => ['type' => 'string'],
                    ],
                    'required' => ['index', 'ok', 'what'],
                    'additionalProperties' => false,
                ]],
            ],
            'required' => ['verdict', 'summary', 'photos'],
            'additionalProperties' => false,
        ], ['model' => config('services.openai.vision_model'), 'reasoning_effort' => null, 'temperature' => 0.1, 'max_tokens' => 1500]);

        if (!in_array($result['verdict'] ?? null, [self::OK, self::SUSPICIOUS, self::MISMATCH], true)) {
            throw new RuntimeException('Неожиданный ответ ИИ при проверке фото');
        }

        // привязываем ответ к конкретным файлам: index из ответа модели — с 1
        $check = [
            'verdict' => $result['verdict'],
            'summary' => $result['summary'],
            'photos' => collect($result['photos'])->map(fn ($p) => [
                'picture' => $pictures[($p['index'] ?? 0) - 1]->pictures ?? null,
                'ok' => (bool) ($p['ok'] ?? true),
                'what' => (string) ($p['what'] ?? ''),
            ])->filter(fn ($p) => $p['picture'])->values()->all(),
            'checked_photos' => $pictures->count(),
        ];

        $announ->forceFill(['photo_check' => $check, 'photo_check_at' => now()]);
        $announ->timestamps = false; // updated_at — время правки владельцем, не трогаем
        $announ->save();

        self::notifyStaff($announ, $check);
        ListingReviewer::refreshRisk($announ); // общий риск в очереди модерации учитывает и фото
        return $check;
    }

    /** Подозрительные фото — уведомление каждому модератору и админу; фото в порядке — убираем старое уведомление */
    private static function notifyStaff(Announ $announ, array $check): void
    {
        $query = AnnounNotification::where('announ_id', $announ->id)->where('kind', AnnounNotification::KIND_PHOTO_ALERT);
        if ($check['verdict'] === self::OK) {
            $query->delete();
            return;
        }

        $message = ($check['verdict'] === self::MISMATCH ? 'Фото не соответствуют объявлению: ' : 'Сомнительные фото: ') . $check['summary'];
        foreach (Owner::whereIn('role', ['moderator', 'admin'])->whereNull('blocked_at')->pluck('id') as $staffId) {
            $notification = AnnounNotification::updateOrCreate(
                ['user_id' => $staffId, 'announ_id' => $announ->id, 'kind' => AnnounNotification::KIND_PHOTO_ALERT],
                ['message' => $message, 'read_at' => null],
            );
            try {
                event(new NotifyMeEvent($staffId, AnnounNotifier::payload($notification, $announ)));
            } catch (\Throwable $e) {
                // сокет-сервер не запущен — уведомление всё равно в колокольчике
            }
        }
    }
}
