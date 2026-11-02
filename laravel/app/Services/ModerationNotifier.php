<?php

namespace App\Services;

use App\Events\NotifyMe as NotifyMeEvent;
use App\Mail\ModerationResultMail;
use App\Models\Announ;
use App\Models\AnnounNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Сообщает владельцу решение модератора: колокольчик на сайте + websocket + письмо.
 */
class ModerationNotifier
{
    public static function notifyOwner(Announ $announ, string $kind, ?string $reason = null): void
    {
        // в колокольчике держим только последнее решение по объявлению
        AnnounNotification::where('user_id', $announ->owner_id)->where('announ_id', $announ->id)
            ->whereIn('kind', [AnnounNotification::KIND_APPROVED, AnnounNotification::KIND_REJECTED, AnnounNotification::KIND_NEEDS_CHANGES])
            ->delete();

        $notification = AnnounNotification::create([
            'user_id' => $announ->owner_id,
            'announ_id' => $announ->id,
            'kind' => $kind,
            'message' => $reason,
        ]);

        try {
            event(new NotifyMeEvent($announ->owner_id, AnnounNotifier::payload($notification, $announ)));
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить решение модерации по websocket', ['announ_id' => $announ->id, 'error' => $e->getMessage()]);
        }

        $owner = $announ->owner;
        if (!$owner?->email) {
            return;
        }
        $send = function () use ($owner, $announ, $kind, $reason) {
            try {
                Mail::to($owner->email)->send(new ModerationResultMail($announ, $kind, $reason, $owner->name));
            } catch (\Throwable $e) {
                Log::warning('Не удалось отправить письмо о модерации', ['email' => $owner->email, 'error' => $e->getMessage()]);
            }
        };
        app()->runningInConsole() ? $send() : dispatch($send)->afterResponse();
    }
}
