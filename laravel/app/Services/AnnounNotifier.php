<?php

namespace App\Services;

use App\Events\NotifyMe as NotifyMeEvent;
use App\Mail\NewAnnounMatchMail;
use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\NotifyMe;
use App\Models\Owner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Рассылает уведомления подписчикам, чьи сохранённые фильтры подходят под новое объявление.
 */
class AnnounNotifier
{
    public static function dispatchFor(Announ $announ): int
    {
        $sent = 0;
        $emails = []; // [email, имя, описание поиска] — отправим после ответа владельцу

        // Владельцу о его же объявлении не сообщаем
        $subscriptions = NotifyMe::where('user_id', '!=', $announ->owner_id)->get();

        foreach ($subscriptions as $subscription) {
            $filters = is_array($subscription->filters) ? $subscription->filters : (json_decode((string) $subscription->filters, true) ?: []);

            // Та же логика, что и в поиске: проверяем, нашлось бы это объявление по фильтрам подписки
            $matches = AnnounFilter::apply(Announ::query()->whereKey($announ->id), $filters)->exists();
            if (!$matches) {
                continue;
            }

            $notification = AnnounNotification::firstOrCreate(
                ['user_id' => $subscription->user_id, 'announ_id' => $announ->id, 'kind' => AnnounNotification::KIND_MATCH],
                ['notify_me_id' => $subscription->id]
            );
            if (!$notification->wasRecentlyCreated) {
                continue; // уже уведомили по другой подписке или при прошлом одобрении (после правки)
            }
            $sent++;

            // websocket — только "доставка в реальном времени". Если сервер сокетов недоступен,
            // уведомление всё равно лежит в БД, а объявление не должно падать с 500.
            try {
                event(new NotifyMeEvent($subscription->user_id, self::payload($notification, $announ)));
            } catch (\Throwable $e) {
                Log::warning('Не удалось отправить уведомление по websocket', ['user_id' => $subscription->user_id, 'error' => $e->getMessage()]);
            }

            $subscriber = Owner::find($subscription->user_id);
            if ($subscriber?->email) {
                $emails[] = [$subscriber->email, $subscriber->name, AnnounFilter::describe($filters)];
            }
        }

        if ($emails) {
            self::sendEmails($announ, $emails);
        }

        return $sent;
    }

    /**
     * Письма на почту подписчикам. Отправка через Gmail занимает 1–2 секунды на письмо, поэтому
     * в веб-запросе она идёт после того, как владелец уже получил ответ «объявление опубликовано».
     */
    private static function sendEmails(Announ $announ, array $emails): void
    {
        $picture = $announ->pictures()->first()?->pictures;
        $imageUrl = $picture ? url('/api/image/small/' . $picture) : null;

        $send = function () use ($announ, $emails, $imageUrl) {
            foreach ($emails as [$email, $name, $label]) {
                try {
                    Mail::to($email)->send(new NewAnnounMatchMail($announ, $label, $name, $imageUrl));
                } catch (\Throwable $e) {
                    Log::warning('Не удалось отправить письмо о новом объявлении', ['email' => $email, 'error' => $e->getMessage()]);
                }
            }
        };

        if (app()->runningInConsole()) {
            $send();
        } else {
            dispatch($send)->afterResponse();
        }
    }

    public static function payload(AnnounNotification $notification, Announ $announ): array
    {
        $announ->loadMissing(['pictures:id,announ_id,pictures,rotation,position', 'owner:id,name,last_name,avatar,uniq_id,phone_number,email']);
        $data = $announ->toArray();
        // страница объявления (/show_announ-...) ждёт фото в поле image
        $data['image'] = $data['pictures'];
        $data['picture'] = $data['pictures'][0]['pictures'] ?? null;

        return [
            'id' => $notification->id,
            'kind' => $notification->kind ?? AnnounNotification::KIND_MATCH,
            'message' => $notification->message,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
            'announ' => $data,
        ];
    }
}
