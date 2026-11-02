<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotifyMe as NotifyMeEvent;
use App\Http\Controllers\Controller;
use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\PromotionRequest;
use App\Models\SiteSetting;
use App\Services\AnnounNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Продвижение глазами админа: заявки (оплата получена → включить / отклонить), тарифы и реквизиты.
 */
class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->input('status'), [PromotionRequest::PENDING, PromotionRequest::APPROVED, PromotionRequest::REJECTED], true)
            ? $request->input('status') : PromotionRequest::PENDING;

        $items = PromotionRequest::with([
                'announ:id,title,type_object,price,deal_type,rental_type,promo,promo_until,owner_id',
                'announ.pictures:id,announ_id,pictures',
                'owner:id,name,last_name,email,phone_number,avatar',
            ])
            ->where('status', $status)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json([
            'items' => $items,
            'counts' => PromotionRequest::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status'),
            'plans' => PromotionRequest::plans(),
            'requisites' => PromotionRequest::requisites(),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $promotion = PromotionRequest::where('status', PromotionRequest::PENDING)->findOrFail($id);
        $announ = Announ::anyStatus()->findOrFail($promotion->announ_id);

        DB::transaction(function () use ($promotion, $announ, $request) {
            $active = $announ->promo_active;
            if ($active === $promotion->plan || ($active === 'vip' && $promotion->plan === 'top')) {
                // тот же тариф ещё идёт — продлеваем от его конца; VIP при оплате ТОП не понижаем, а продлеваем
                $plan = $active;
                $from = $announ->promo_until;
            } else {
                // нет продвижения или ТОП → VIP: новый тариф с этого момента
                $plan = $promotion->plan;
                $from = now();
            }
            $announ->forceFill(['promo' => $plan, 'promo_until' => $from->copy()->addDays($promotion->days)])->save();

            $promotion->forceFill(['status' => PromotionRequest::APPROVED, 'decided_by' => $request->user()->id, 'decided_at' => now()])->save();
        });

        $this->notify($announ, AnnounNotification::KIND_PROMOTED, ($announ->promo === 'vip' ? 'VIP' : 'ТОП') . ' до ' . $announ->promo_until->format('d.m.Y'));
        return response()->json(['message' => 'Продвижение включено', 'promo' => $announ->promo, 'promo_until' => $announ->promo_until]);
    }

    public function reject(Request $request, int $id)
    {
        $data = $request->validate(['reason' => 'required|string|min:3|max:300'], ['reason.required' => 'Напишите причину — её увидит владелец']);
        $promotion = PromotionRequest::where('status', PromotionRequest::PENDING)->findOrFail($id);
        $promotion->forceFill([
            'status' => PromotionRequest::REJECTED,
            'reason' => trim($data['reason']),
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
        ])->save();

        if ($announ = Announ::anyStatus()->find($promotion->announ_id)) {
            $this->notify($announ, AnnounNotification::KIND_PROMO_REJECTED, $promotion->reason);
        }
        return response()->json(['message' => 'Заявка отклонена']);
    }

    /** Снять продвижение досрочно (например, по ошибке включили) */
    public function cancel(int $announId)
    {
        $announ = Announ::anyStatus()->findOrFail($announId);
        $announ->forceFill(['promo' => null, 'promo_until' => null])->save();
        return response()->json(['message' => 'Продвижение снято']);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'top_price' => 'required|integer|min:0|max:100000',
            'top_days' => 'required|integer|min:1|max:365',
            'vip_price' => 'required|integer|min:0|max:100000',
            'vip_days' => 'required|integer|min:1|max:365',
            'requisites' => 'nullable|string|max:1000',
        ]);
        foreach (['top_price', 'top_days', 'vip_price', 'vip_days'] as $key) {
            SiteSetting::put('promo_' . $key, $data[$key]);
        }
        SiteSetting::put('promo_requisites', trim((string) ($data['requisites'] ?? '')) ?: null);

        return response()->json(['plans' => PromotionRequest::plans(), 'requisites' => PromotionRequest::requisites()]);
    }

    /** Колокольчик + websocket владельцу — как у решений модерации */
    private function notify(Announ $announ, string $kind, ?string $message): void
    {
        // в колокольчике — только последнее решение по продвижению (и уникальный индекс user+announ+kind)
        AnnounNotification::where('user_id', $announ->owner_id)->where('announ_id', $announ->id)
            ->whereIn('kind', [AnnounNotification::KIND_PROMOTED, AnnounNotification::KIND_PROMO_REJECTED])
            ->delete();
        $notification = AnnounNotification::create([
            'user_id' => $announ->owner_id,
            'announ_id' => $announ->id,
            'kind' => $kind,
            'message' => $message,
        ]);
        try {
            event(new NotifyMeEvent($announ->owner_id, AnnounNotifier::payload($notification, $announ)));
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить уведомление о продвижении', ['announ_id' => $announ->id, 'error' => $e->getMessage()]);
        }
    }
}
