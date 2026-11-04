<?php

namespace App\Services;

use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\ModerationLog;
use App\Models\SiteSetting;

/**
 * Автомодерация по итогам ИИ-проверок (ListingChecks). Режим — site_settings.auto_moderation:
 *  - off     — всё решает модератор;
 *  - approve — чистые объявления публикуются сами, сомнительные (средний/высокий риск, дубликаты,
 *              владелец с отказами) ждут модератора;
 *  - full    — без модератора: высокий риск — отказ с причиной от ИИ, похожие на другие объявления (дубликаты)
 *              ждут модератора, остальное публикуется.
 * Без свежей ИИ-проверки (ИИ не настроен или упал) объявление в любом режиме ждёт модератора.
 * Решение пишется как обычное, но без модератора: moderated_by и moderation_logs.moderator_id — null.
 */
class AutoModerator
{
    public const OFF = 'off';
    public const APPROVE = 'approve';
    public const FULL = 'full';
    public const MODES = [self::OFF, self::APPROVE, self::FULL];

    public static function mode(): string
    {
        $mode = SiteSetting::get('auto_moderation', self::OFF);
        return in_array($mode, self::MODES, true) ? $mode : self::OFF;
    }

    /** Принять решение, если можно без человека. Возвращает новый статус или null — оставить модератору. */
    public static function run(Announ $announ): ?string
    {
        $mode = self::mode();
        if ($mode === self::OFF || $announ->moderation_status !== Announ::PENDING || $announ->trashed()) {
            return null;
        }
        // проверка текста должна быть свежей: владелец мог поправить объявление, пока шла проверка
        if (!self::fresh($announ->ai_review_at, $announ)) {
            return null;
        }

        // полный режим — без модератора: высокий риск (текст, цена или фото) отклоняем, остальное публикуем;
        // похожее на другое объявление — не публикуем само: копию или повторную подачу решает модератор
        if ($mode === self::FULL) {
            if ($announ->ai_risk === 'high') {
                $reason = self::rejectReason($announ);
                self::decide($announ, Announ::REJECTED, $reason);
                ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_REJECTED, $reason);
                return Announ::REJECTED;
            }
            return self::hasDuplicates($announ) ? null : self::approve($announ);
        }

        return $announ->ai_risk === 'low' && self::clean($announ) ? self::approve($announ) : null;
    }

    private static function approve(Announ $announ): string
    {
        self::decide($announ, Announ::APPROVED, null);
        ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_APPROVED);
        AnnounNotifier::dispatchFor($announ);
        return Announ::APPROVED;
    }

    /** Причина для владельца: от проверки текста, если риск в нём; иначе — из-за фото */
    private static function rejectReason(Announ $announ): string
    {
        $text = trim((string) ($announ->ai_review['suggested_reason'] ?? ''));
        if (($announ->ai_review['risk'] ?? null) === 'high' && mb_strlen($text) >= 5) {
            return $text;
        }
        if (($announ->photo_check['verdict'] ?? null) === PhotoChecker::MISMATCH) {
            return trim('Фото не подходят к объявлению. Загрузите настоящие фото объекта. ' . ($announ->photo_check['summary'] ?? ''));
        }
        if ($text) {
            return $text;
        }
        // ИИ не написал причину — перечисляем найденные проблемы, чтобы владелец знал, что исправить
        $issues = implode('; ', array_filter(array_map('trim', $announ->ai_review['issues'] ?? [])));
        return $issues
            ? "Объявление не прошло автоматическую проверку: $issues. Исправьте и отправьте снова."
            : 'Объявление не прошло автоматическую проверку. Проверьте цену, описание и фото и отправьте снова.';
    }

    /** Можно публиковать без человека: фото проверены и в порядке, дубликатов нет, владельцу раньше не отказывали */
    private static function clean(Announ $announ): bool
    {
        if ($announ->pictures()->exists()
            && (($announ->photo_check['verdict'] ?? null) !== PhotoChecker::OK || !self::fresh($announ->photo_check_at, $announ))) {
            return false;
        }
        $owner = $announ->owner;
        if (!$owner || $owner->blocked_at) {
            return false;
        }
        $rejectedBefore = Announ::anyStatus()->withTrashed()->where('owner_id', $owner->id)
            ->where('moderation_status', Announ::REJECTED)->exists();
        if ($rejectedBefore) {
            return false;
        }
        return !self::hasDuplicates($announ);
    }

    private static function hasDuplicates(Announ $announ): bool
    {
        $duplicates = DuplicateFinder::find($announ);
        return $duplicates['photos'] || $duplicates['text'];
    }

    private static function fresh($checkedAt, Announ $announ): bool
    {
        return $checkedAt && (!$announ->updated_at || $checkedAt->gte($announ->updated_at));
    }

    private static function decide(Announ $announ, string $status, ?string $reason): void
    {
        $announ->forceFill([
            'moderation_status' => $status,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => null,
        ]);
        $announ->timestamps = false; // updated_at — время правки владельцем
        $announ->save();

        ModerationLog::create([
            'moderator_id' => null,
            'action' => $status === Announ::APPROVED ? 'approve' : 'reject',
            'announ_id' => $announ->id,
            'target_user_id' => $announ->owner_id,
            'reason' => $reason,
        ]);
    }
}
