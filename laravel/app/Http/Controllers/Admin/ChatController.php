<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ModerationLog;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Переписки пользователей для администратора: кто с кем общается и сами сообщения (только чтение).
 * Нужно для разбора жалоб и мошенничества. Каждое открытие переписки записывается в moderation_logs,
 * чтобы доступ к личным сообщениям был под контролем.
 */
class ChatController extends Controller
{
    private const USER_FIELDS = ['id', 'name', 'last_name', 'email', 'phone_number', 'avatar', 'uniq_id', 'role', 'blocked_at'];

    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:100',
            'user_id' => 'nullable|integer',
        ]);

        // Пара собеседников без учёта направления: (5, 9) и (9, 5) — одна переписка
        $query = DB::table('message_users')
            ->whereNull('deleted_at')
            ->selectRaw('LEAST(owner_id, friend_id) as a, GREATEST(owner_id, friend_id) as b, COUNT(*) as messages_count, MAX(message_id) as last_message_id, MAX(created_at) as last_at')
            ->groupByRaw('LEAST(owner_id, friend_id), GREATEST(owner_id, friend_id)')
            ->orderByDesc('last_at');

        $userIds = null;
        if (!empty($data['user_id'])) {
            $userIds = [(int) $data['user_id']];
        } elseif (!empty($data['q'])) {
            $q = trim($data['q']);
            $userIds = Owner::where(fn ($s) => $s->where('name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")->orWhere('phone_number', 'like', "%$q%"))->pluck('id')->all();
        }
        if ($userIds !== null) {
            $query->where(fn ($s) => $s->whereIn('owner_id', $userIds ?: [0])->orWhereIn('friend_id', $userIds ?: [0]));
        }

        $page = $query->paginate(30);
        $rows = collect($page->items());

        $users = Owner::whereIn('id', $rows->pluck('a')->merge($rows->pluck('b'))->unique())->get(self::USER_FIELDS)->keyBy('id');
        $last = Message::whereIn('id', $rows->pluck('last_message_id'))->get(['id', 'message', 'type', 'user_id'])->keyBy('id');

        $page->setCollection($rows->map(fn ($r) => [
            'a' => $users[$r->a] ?? ['id' => $r->a, 'name' => 'Удалённый пользователь'],
            'b' => $users[$r->b] ?? ['id' => $r->b, 'name' => 'Удалённый пользователь'],
            'messages_count' => (int) $r->messages_count,
            'last_at' => $r->last_at,
            'last_message' => isset($last[$r->last_message_id]) ? $this->preview($last[$r->last_message_id]) : null,
            'last_from' => $last[$r->last_message_id]->user_id ?? null,
        ]));

        return response()->json($page);
    }

    public function show(Request $request, int $a, int $b)
    {
        $messageIds = DB::table('message_users')->whereNull('deleted_at')
            ->where(fn ($q) => $q->where('owner_id', $a)->where('friend_id', $b))
            ->orWhere(fn ($q) => $q->where('owner_id', $b)->where('friend_id', $a))
            ->pluck('message_id');

        // последние 300 — для разбора жалобы этого достаточно, и страница не грузит годы переписки
        $messages = Message::whereIn('id', $messageIds)->orderByDesc('id')->limit(300)->get(['id', 'message', 'type', 'user_id', 'time', 'created_at'])->reverse()->values();

        ModerationLog::create([
            'moderator_id' => $request->user()->id,
            'action' => 'view_chat',
            'target_user_id' => $a,
            'reason' => "переписка #$a и #$b",
        ]);

        return response()->json([
            'users' => Owner::whereIn('id', [$a, $b])->get(self::USER_FIELDS)->keyBy('id'),
            'messages' => $messages,
            'total' => $messageIds->count(),
        ]);
    }

    private function preview(Message $m): string
    {
        return match ($m->type) {
            'picture' => '📷 Фото',
            'video' => '🎬 Видео',
            default => mb_strimwidth((string) $m->message, 0, 90, '…'),
        };
    }
}
