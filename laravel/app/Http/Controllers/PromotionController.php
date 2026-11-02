<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\PromotionRequest;
use Illuminate\Http\Request;

/**
 * Продвижение глазами владельца: тарифы, заявка на ТОП/VIP, статус заявок.
 * Оплата пока вручную: владелец переводит по реквизитам и пишет номер перевода, админ проверяет и включает.
 */
class PromotionController extends Controller
{
    public function plans()
    {
        return response()->json([
            'plans' => PromotionRequest::plans(),
            'requisites' => PromotionRequest::requisites(),
        ]);
    }

    /** Последняя заявка по каждому объявлению владельца — для «Моих объявлений» */
    public function mine(Request $request)
    {
        $latest = PromotionRequest::where('owner_id', $request->user()->id)
            ->orderByDesc('id')
            ->get(['id', 'announ_id', 'plan', 'days', 'price', 'status', 'reason', 'created_at', 'decided_at'])
            ->unique('announ_id')
            ->keyBy('announ_id');

        return response()->json($latest);
    }

    public function store(Request $request, int $id)
    {
        $data = $request->validate([
            'plan' => 'required|in:' . implode(',', Announ::PROMO_PLANS),
            'payment_note' => 'required|string|min:3|max:300',
        ], [
            'payment_note.required' => 'Укажите номер перевода или с какой карты/кошелька оплатили — так админ найдёт платёж',
            'payment_note.min' => 'Укажите номер перевода или с какой карты/кошелька оплатили — так админ найдёт платёж',
        ]);

        // только своё объявление
        $announ = Announ::anyStatus()->where('owner_id', $request->user()->id)->findOrFail($id);
        // продвигать есть смысл только то, что видно в поиске
        if ($announ->moderation_status !== Announ::APPROVED || $announ->sold_at) {
            return response()->json(['message' => 'Продвигать можно только опубликованное объявление'], 422);
        }
        if (PromotionRequest::where('announ_id', $announ->id)->where('status', PromotionRequest::PENDING)->exists()) {
            return response()->json(['message' => 'Заявка по этому объявлению уже ждёт проверки оплаты'], 422);
        }

        $plan = PromotionRequest::plans()[$data['plan']];
        $promotion = PromotionRequest::create([
            'announ_id' => $announ->id,
            'owner_id' => $announ->owner_id,
            'plan' => $data['plan'],
            'days' => $plan['days'],
            'price' => $plan['price'],
            'payment_note' => trim($data['payment_note']),
        ]);

        return response()->json([
            'message' => 'Заявка отправлена. Как только администратор увидит оплату, объявление поднимется — придёт уведомление.',
            'request' => $promotion,
        ], 201);
    }
}
