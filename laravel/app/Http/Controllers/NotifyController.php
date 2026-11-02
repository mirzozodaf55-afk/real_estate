<?php

namespace App\Http\Controllers;

use App\Models\AnnounNotification;
use App\Models\NotifyMe;
use App\Services\AnnounFilter;
use App\Services\AnnounNotifier;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    private const MAX_SUBSCRIPTIONS = 20;

    // --- Подписки ("сохранённые поиски")

    public function index(Request $request)
    {
        return NotifyMe::where('user_id', $request->user()->id)->latest()->get(['id', 'filters', 'created_at']);
    }

    public function notifyMe(Request $request)
    {
        $request->validate(['filters' => 'nullable|array']);
        $user = $request->user();
        $filters = AnnounFilter::normalize($request->input('filters', []));

        // Такая же подписка уже есть — не плодим дубли
        foreach (NotifyMe::where('user_id', $user->id)->get() as $existing) {
            $existingFilters = AnnounFilter::normalize(is_array($existing->filters) ? $existing->filters : []);
            if ($existingFilters == $filters) {
                return response()->json(['message' => 'Вы уже подписаны на этот поиск', 'existing' => true, 'data' => $existing]);
            }
        }

        if (NotifyMe::where('user_id', $user->id)->count() >= self::MAX_SUBSCRIPTIONS) {
            return response()->json(['message' => 'Можно сохранить не больше ' . self::MAX_SUBSCRIPTIONS . ' поисков. Удалите ненужные в разделе «Сохранённые поиски».'], 422);
        }

        $notify = NotifyMe::create(['user_id' => $user->id, 'filters' => $filters]);

        return response()->json(['message' => 'Подписка сохранена', 'existing' => false, 'data' => $notify], 201);
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = NotifyMe::where('user_id', $request->user()->id)->where('id', $id)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Подписка не найдена'], 404);
        }
        return response()->json(['message' => 'Подписка удалена']);
    }

    // --- Уведомления

    public function notifications(Request $request)
    {
        $userId = $request->user()->id;

        $visible = fn () => AnnounNotification::where('user_id', $userId)
            ->whereHas('announ') // объявление могли удалить
            // «новое по подписке» показываем, только пока объявление одобрено (модератор мог потом отклонить)
            ->where(fn ($q) => $q->where('kind', '!=', AnnounNotification::KIND_MATCH)
                ->orWhereHas('announ', fn ($a) => $a->where('moderation_status', \App\Models\Announ::APPROVED)));

        $items = $visible()
            ->with('announ')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => AnnounNotifier::payload($n, $n->announ));

        return response()->json([
            'data' => $items,
            'unread' => $visible()->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request)
    {
        $query = AnnounNotification::where('user_id', $request->user()->id)->whereNull('read_at');
        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->input('ids'));
        }
        $query->update(['read_at' => now()]);
        return response()->json(['message' => 'ok']);
    }
}
