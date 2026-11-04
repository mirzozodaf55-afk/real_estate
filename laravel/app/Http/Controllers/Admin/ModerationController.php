<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\ConditionAnnoun;
use App\Models\Conditions;
use App\Models\Infrastructure;
use App\Models\InfrastructureAnnoun;
use App\Models\ModerationLog;
use App\Models\Owner;
use App\Models\RejectionReason;
use App\Models\SiteSetting;
use App\Services\AutoModerator;
use App\Services\DuplicateFinder;
use App\Services\AnnounNotifier;
use App\Services\ModerationNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Очередь модерации объявлений (роли moderator и admin).
 */
class ModerationController extends Controller
{
    private const OWNER_FIELDS = 'owner:id,name,last_name,email,phone_number,avatar,uniq_id,role,created_at';
    // очередь: сначала высокий риск по мнению ИИ, потом средний, низкий, не проверенные (внутри — кто дольше ждёт)
    private const QUEUE_ORDER = "CASE ai_risk WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 ELSE 3 END";

    public function stats()
    {
        $byStatus = Announ::anyStatus()->selectRaw('moderation_status, count(*) as c')
            ->groupBy('moderation_status')->pluck('c', 'moderation_status');

        return response()->json([
            'pending' => (int) ($byStatus[Announ::PENDING] ?? 0),
            'approved' => (int) ($byStatus[Announ::APPROVED] ?? 0),
            'rejected' => (int) ($byStatus[Announ::REJECTED] ?? 0),
            'needs_changes' => (int) ($byStatus[Announ::NEEDS_CHANGES] ?? 0),
            // ждут проверки с высоким риском по мнению ИИ (текст, цена или фото)
            'high_risk' => Announ::anyStatus()->where('moderation_status', Announ::PENDING)->where('ai_risk', 'high')->count(),
            'users' => Owner::count(),
            'staff' => Owner::whereIn('role', ['moderator', 'admin'])->count(),
            'blocked' => Owner::whereNotNull('blocked_at')->count(),
            // ждут проверки и ИИ сомневается в фото — модератору стоит начать с них
            'photo_alerts' => Announ::anyStatus()->where('moderation_status', Announ::PENDING)
                ->whereIn('photo_check->verdict', [\App\Services\PhotoChecker::SUSPICIOUS, \App\Services\PhotoChecker::MISMATCH])->count(),
            'sold' => Announ::anyStatus()->whereNotNull('sold_at')->count(),
            'moderated_today' => ModerationLog::whereIn('action', ['approve', 'reject'])->where('created_at', '>=', today())->count(),
            // самое старое из ждущих — видно, не копится ли очередь
            'oldest_pending_at' => Announ::anyStatus()->where('moderation_status', Announ::PENDING)->min('updated_at'),
            'auto_moderation' => AutoModerator::mode(),
            'auto_moderated_today' => ModerationLog::whereNull('moderator_id')->whereIn('action', ['approve', 'reject', 'needs_changes'])->where('created_at', '>=', today())->count(),
            // без ИИ автомодерации не на что опереться — всё останется в очереди
            'ai_enabled' => \App\Services\OpenAi::enabled(),
            'vision_enabled' => \App\Services\PhotoChecker::enabled(),
        ]);
    }

    /** Режим автомодерации (только админ): off / approve / full — см. App\Services\AutoModerator */
    public function autoModeration(Request $request)
    {
        $data = $request->validate(['mode' => 'required|in:' . implode(',', AutoModerator::MODES)]);
        SiteSetting::put('auto_moderation', $data['mode']);
        ModerationLog::create(['moderator_id' => $request->user()->id, 'action' => 'auto_moderation', 'reason' => $data['mode']]);

        return response()->json(['auto_moderation' => AutoModerator::mode()]);
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => 'nullable|in:' . implode(',', Announ::STATUSES),
            'risk' => 'nullable|in:high,medium,low,none',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'q' => 'nullable|string|max:100',
        ]);
        $status = $data['status'] ?? Announ::PENDING;

        $query = Announ::anyStatus()
            ->where('moderation_status', $status)
            ->with(['pictures:id,announ_id,pictures,rotation,position', self::OWNER_FIELDS]);

        if (!empty($data['risk'])) {
            $data['risk'] === 'none' ? $query->whereNull('ai_risk') : $query->where('ai_risk', $data['risk']);
        }
        // дата — когда объявление подали или последний раз правили (по ней оно и встало в очередь)
        if (!empty($data['from'])) {
            $query->where('updated_at', '>=', \Carbon\Carbon::parse($data['from'])->startOfDay());
        }
        if (!empty($data['to'])) {
            $query->where('updated_at', '<=', \Carbon\Carbon::parse($data['to'])->endOfDay());
        }

        if (!empty($data['q'])) {
            $q = trim($data['q']);
            $query->where(function ($sub) use ($q) {
                if (ctype_digit($q)) {
                    $sub->where('id', (int) $q);
                }
                $sub->orWhere('title', 'like', "%$q%")->orWhere('description', 'like', "%$q%")
                    ->orWhereHas('owner', fn ($o) => $o->where('email', 'like', "%$q%")->orWhere('phone_number', 'like', "%$q%"));
            });
        }

        if ($status === Announ::PENDING) {
            // очередь: сначала высокий риск по мнению ИИ, потом средний, низкий, не проверенные; внутри — кто дольше ждёт
            $query->orderByRaw(self::QUEUE_ORDER)->orderBy('updated_at');
        } else {
            // архив — сначала свежие решения
            $query->orderByDesc('moderated_at');
        }

        $page = $query->paginate(20);
        $page->getCollection()->each->makeVisible(Announ::STAFF_FIELDS);
        return response()->json($page);
    }

    public function show(int $id)
    {
        $announ = $this->find($id);
        $announ->load(['pictures:id,announ_id,pictures,rotation,position', self::OWNER_FIELDS]);

        $data = $announ->makeVisible(Announ::STAFF_FIELDS)->toArray();
        $data['conditions'] = Conditions::whereIn('id', ConditionAnnoun::where('announ_id', $id)->select('condition_id'))->pluck('name');
        $data['infrastructure'] = Infrastructure::whereIn('id', InfrastructureAnnoun::where('announ_id', $id)->select('infrastructure_id'))->pluck('name');
        // чтобы модератор видел, новичок это или владелец, которому уже отказывали
        $data['owner_stats'] = [
            'total' => Announ::anyStatus()->withTrashed()->where('owner_id', $announ->owner_id)->count(),
            'rejected' => Announ::anyStatus()->withTrashed()->where('owner_id', $announ->owner_id)->where('moderation_status', Announ::REJECTED)->count(),
        ];
        $data['duplicates'] = DuplicateFinder::find($announ);
        $data['history'] = ModerationLog::where('announ_id', $id)->with('moderator:id,name,last_name,email')->latest('id')->limit(20)->get();

        return response()->json(['data' => $data]);
    }

    /** Проверить (или перепроверить) фото ИИ прямо сейчас — по кнопке в карточке модерации */
    public function photoCheck(int $id)
    {
        if (!\App\Services\PhotoChecker::enabled()) {
            return response()->json(['message' => 'Проверка фото выключена: не задан OPENAI_VISION_MODEL'], 503);
        }
        try {
            $check = \App\Services\PhotoChecker::check($this->find($id));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
        return $check
            ? response()->json(['photo_check' => $check])
            : response()->json(['message' => 'У объявления нет фото'], 422);
    }

    public function approve(Request $request, int $id)
    {
        $announ = $this->find($id);
        if (!$this->decide($request, $announ, Announ::APPROVED, null)) {
            return response()->json(['message' => 'Объявление уже одобрено', 'next_id' => $this->nextPendingId($announ->id)]);
        }

        ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_APPROVED);
        // подписчики «Уведомить меня» узнают о объявлении только теперь, когда его видно в поиске
        if (!$announ->trashed()) {
            AnnounNotifier::dispatchFor($announ);
        }

        return response()->json(['message' => 'Объявление одобрено', 'next_id' => $this->nextPendingId($announ->id)]);
    }

    public function reject(Request $request, int $id)
    {
        $reason = $this->reasonFromRequest($request);
        $announ = $this->find($id);
        if (!$this->decide($request, $announ, Announ::REJECTED, $reason)) {
            return response()->json(['message' => 'Объявление уже отклонено с этой причиной', 'next_id' => $this->nextPendingId($announ->id)]);
        }

        ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_REJECTED, $announ->moderation_reason);

        return response()->json(['message' => 'Объявление отклонено', 'next_id' => $this->nextPendingId($announ->id)]);
    }

    /** Вернуть на доработку: объявление не отклонено, владелец правит по комментарию и отправляет снова */
    public function needsChanges(Request $request, int $id)
    {
        $data = $request->validate(['comment' => 'required|string|min:5|max:500'], [
            'comment.required' => 'Напишите, что нужно исправить — это увидит владелец',
            'comment.min' => 'Опишите подробнее, что нужно исправить',
        ]);
        $announ = $this->find($id);
        if (!$this->decide($request, $announ, Announ::NEEDS_CHANGES, trim($data['comment']))) {
            return response()->json(['message' => 'Уже отправлено на доработку с этим комментарием', 'next_id' => $this->nextPendingId($announ->id)]);
        }

        ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_NEEDS_CHANGES, $announ->moderation_reason);

        return response()->json(['message' => 'Отправлено на доработку', 'next_id' => $this->nextPendingId($announ->id)]);
    }

    /** Массово: одобрить или отклонить (с одной причиной) выбранные объявления */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:50',
            'ids.*' => 'integer',
            'action' => 'required|in:approve,reject',
        ]);
        $reason = $data['action'] === 'reject' ? $this->reasonFromRequest($request) : null;

        $announs = Announ::anyStatus()->withTrashed()->whereIn('id', $data['ids'])->get();
        $count = 0;
        foreach ($announs as $announ) {
            // уже с таким решением (например, другой модератор успел раньше) — владельцу второй раз не пишем
            if (!DB::transaction(fn () => $this->decide($request, $announ, $data['action'] === 'approve' ? Announ::APPROVED : Announ::REJECTED, $reason))) {
                continue;
            }
            $count++;
            if ($data['action'] === 'approve') {
                ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_APPROVED);
                if (!$announ->trashed()) {
                    AnnounNotifier::dispatchFor($announ);
                }
            } else {
                ModerationNotifier::notifyOwner($announ, AnnounNotification::KIND_REJECTED, $reason);
            }
        }

        return response()->json([
            'message' => ($data['action'] === 'approve' ? 'Одобрено: ' : 'Отклонено: ') . $count,
            'count' => $count,
        ]);
    }

    /**
     * Причина отказа: готовая из справочника (reason_id) + уточнение модератора (reason), или только свой текст («Другое»).
     * Готовая причина уходит владельцу по-русски (text): английский перевод (text_en) — только для модератора на сайте.
     */
    private function reasonFromRequest(Request $request): string
    {
        $data = $request->validate([
            'reason_id' => 'nullable|integer|exists:rejection_reasons,id',
            'reason' => 'nullable|string|max:500',
        ], ['reason_id.exists' => 'Такой причины отказа нет — обновите страницу']);
        $preset = null;
        if (!empty($data['reason_id']) && ($r = RejectionReason::find($data['reason_id']))) {
            $preset = $r->text;
        }
        $own = trim((string) ($data['reason'] ?? ''));

        if (!$preset && mb_strlen($own) < 5) {
            abort(response()->json([
                'message' => 'Выберите причину или опишите её сами — её увидит владелец',
                'errors' => ['reason' => ['Выберите причину или опишите её сами — её увидит владелец']],
            ], 422));
        }
        return trim($preset . ($preset && $own ? ' ' : '') . $own);
    }

    /** Записать решение. false — у объявления уже такое решение: ничего не меняем, в журнал не пишем */
    private function decide(Request $request, Announ $announ, string $status, ?string $reason): bool
    {
        if ($announ->moderation_status === $status && $announ->moderation_reason === $reason) {
            return false;
        }
        $announ->forceFill([
            'moderation_status' => $status,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);
        // updated_at не трогаем: по нему владелец видит, когда сам менял объявление
        $announ->timestamps = false;
        $announ->save();

        ModerationLog::create([
            'moderator_id' => $request->user()->id,
            'action' => match ($status) { Announ::APPROVED => 'approve', Announ::NEEDS_CHANGES => 'needs_changes', default => 'reject' },
            'announ_id' => $announ->id,
            'target_user_id' => $announ->owner_id,
            'reason' => $reason,
        ]);
        return true;
    }

    /** Следующее в очереди — чтобы модератор шёл по списку, не возвращаясь к нему */
    private function nextPendingId(int $exceptId): ?int
    {
        return Announ::anyStatus()->where('moderation_status', Announ::PENDING)
            ->where('id', '!=', $exceptId)->orderByRaw(self::QUEUE_ORDER)->orderBy('updated_at')->value('id');
    }

    private function find(int $id): Announ
    {
        return Announ::anyStatus()->withTrashed()->findOrFail($id);
    }
}
