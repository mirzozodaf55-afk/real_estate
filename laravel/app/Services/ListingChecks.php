<?php

namespace App\Services;

use App\Models\Announ;
use Illuminate\Support\Facades\Log;

/**
 * Всё, что делаем с объявлением после подачи или правки, пока оно ждёт модератора — после ответа пользователю:
 * отпечатки фото (для поиска дубликатов), ИИ-проверка фото, ИИ-проверка текста и цены → общий риск для очереди.
 * Сбой одной проверки не мешает остальным; не проверенное можно перепроверить кнопкой в карточке модерации.
 */
class ListingChecks
{
    public static function dispatch(int $announId): void
    {
        $run = function () use ($announId) {
            $announ = Announ::anyStatus()->withTrashed()->find($announId);
            if (!$announ) {
                return;
            }
            try {
                DuplicateFinder::hashPhotos($announ);
            } catch (\Throwable $e) {
                Log::warning('Не удалось посчитать pHash фото', ['announ_id' => $announId, 'error' => $e->getMessage()]);
            }
            if (PhotoChecker::enabled()) {
                try {
                    PhotoChecker::check($announ);
                } catch (\Throwable $e) {
                    Log::warning('Проверка фото ИИ не удалась', ['announ_id' => $announId, 'error' => $e->getMessage()]);
                }
            }
            if (OpenAi::enabled()) {
                try {
                    ListingReviewer::review($announ->refresh(), true);
                } catch (\Throwable $e) {
                    Log::warning('ИИ-проверка текста и цены не удалась', ['announ_id' => $announId, 'error' => $e->getMessage()]);
                }
            }
            ListingReviewer::refreshRisk($announ->refresh());
        };
        app()->runningInConsole() ? $run() : dispatch($run)->afterResponse();
    }
}
