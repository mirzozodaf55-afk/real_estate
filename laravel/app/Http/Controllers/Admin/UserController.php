<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announ;
use App\Models\ModerationLog;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Пользователи и роли (только admin).
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:100',
            'role' => ['nullable', Rule::in(Owner::ROLES)],
            'blocked' => 'nullable|boolean',
        ]);

        $query = Owner::query()
            ->select('id', 'name', 'last_name', 'email', 'phone_number', 'avatar', 'uniq_id', 'role', 'blocked_at', 'block_reason', 'last_seen_at', 'created_at')
            ->addSelect([
                'announs_count' => Announ::anyStatus()->withTrashed()->selectRaw('count(*)')->whereColumn('owner_id', 'owners.id'),
            ]);

        if (!empty($data['q'])) {
            $q = trim($data['q']);
            $query->where(fn ($sub) => $sub->where('name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")->orWhere('phone_number', 'like', "%$q%"));
        }
        if (!empty($data['role'])) {
            $query->where('role', $data['role']);
        }
        if (!empty($data['blocked'])) {
            $query->whereNotNull('blocked_at');
        }

        // сначала команда сайта, потом остальные — свежие наверху
        $page = $query->orderByRaw("FIELD(role, 'admin', 'moderator', 'user')")->latest('id')->paginate(20);
        $page->getCollection()->each->makeVisible('block_reason');
        return response()->json($page);
    }

    public function block(Request $request, int $id)
    {
        $data = $request->validate(['reason' => 'required|string|min:3|max:500'], [
            'reason.required' => 'Укажите причину — её увидит пользователь при входе',
        ]);
        $user = Owner::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Нельзя заблокировать себя'], 422);
        }
        if ($user->hasRole('admin')) {
            // сначала снять роль — так случайным кликом не заблокировать другого администратора
            return response()->json(['message' => 'Администратора заблокировать нельзя — сначала смените ему роль'], 422);
        }

        $user->forceFill(['blocked_at' => now(), 'block_reason' => trim($data['reason'])])->save();
        // выкидываем со всех устройств; его объявления сразу пропадают из поиска (PublicScope)
        $user->tokens()->delete();

        ModerationLog::create([
            'moderator_id' => $request->user()->id,
            'action' => 'block',
            'target_user_id' => $user->id,
            'reason' => $user->block_reason,
        ]);

        return response()->json(['message' => 'Пользователь заблокирован', 'data' => $user->only('id', 'blocked_at') + ['block_reason' => $user->block_reason]]);
    }

    public function unblock(Request $request, int $id)
    {
        $user = Owner::findOrFail($id);
        $user->forceFill(['blocked_at' => null, 'block_reason' => null])->save();

        ModerationLog::create([
            'moderator_id' => $request->user()->id,
            'action' => 'unblock',
            'target_user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Пользователь разблокирован']);
    }

    public function setRole(Request $request, int $id)
    {
        $data = $request->validate(['role' => ['required', Rule::in(Owner::ROLES)]]);
        if ($id === $request->user()->id) {
            // иначе последний админ может случайно лишить себя доступа
            return response()->json(['message' => 'Свою роль менять нельзя'], 422);
        }

        $user = Owner::findOrFail($id);
        $user->forceFill(['role' => $data['role']])->save();

        ModerationLog::create([
            'moderator_id' => $request->user()->id,
            'action' => 'role:' . $data['role'],
            'target_user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Роль изменена', 'data' => $user->only('id', 'role')]);
    }
}
