<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\Likes;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LikesController extends Controller
{
    /**
     * Сердечко: ставит отметку «в избранном», если её нет, и снимает, если есть.
     * Поставить можно только на опубликованное объявление; снять — всегда (даже если его уже скрыли).
     */
    public function setLikes(Request $request){
        $data = $request->validate(['announ_id' => 'required|integer']);
        $userId = $request->user()->id;
        $announId = (int) $data['announ_id'];

        $existing = Likes::where('user_id', $userId)->where('announ_id', $announId);
        if ($existing->exists()) {
            $existing->delete();
            $liked = false;
        } else {
            // чужое скрытое (на проверке, снятое, проданное) или несуществующее — не отмечаем
            if (!Announ::whereKey($announId)->exists()) {
                return response()->json(['message' => 'Объявление недоступно'], 404);
            }
            try {
                Likes::create(['user_id' => $userId, 'announ_id' => $announId]);
            } catch (QueryException $e) {
                // двойной клик: параллельный запрос уже поставил отметку (уникальный индекс user_id+announ_id)
            }
            $liked = true;
        }

        return response()->json([
            'likes-count' => Likes::where('announ_id', $announId)->count(),
            'liked' => $liked,
        ]);
    }

    /**
     * Избранное: опубликованные — полностью; ставшие недоступными (снято, продано, на проверке после правки) —
     * отдельной пометкой и без содержимого, которое модератор мог ещё не видеть: их можно только убрать.
     */
    public function getFavorite(Request $request){
        $likes = Likes::where('user_id', $request->user()->id)->orderByDesc('id')->pluck('announ_id');
        if ($likes->isEmpty()) {
            return [];
        }

        $public = Announ::with('pictures:id,announ_id,pictures,rotation,position')
            ->withCount('likes')
            ->whereIn('id', $likes)
            ->get()
            ->keyBy('id');

        // всё, что не прошло публичный фильтр: снятые (soft delete), проданные, на проверке, у заблокированных
        $hidden = Announ::anyStatus()->withTrashed()
            ->whereIn('id', $likes->diff($public->keys()))
            ->get(['id', 'type_object', 'deal_type', 'rental_type', 'sold_at', 'deleted_at', 'moderation_status'])
            ->keyBy('id');

        // порядок — как отмечали (новые сверху); отметки на совсем удалённые объявления просто пропускаем
        return $likes->map(function ($id) use ($public, $hidden) {
            if ($announ = $public->get($id)) {
                $data = $announ->toArray();
                $data['images'] = $data['pictures']; // карточки и страница объявления ждут фото в поле images
                $data['available'] = true;
                return $data;
            }
            if ($announ = $hidden->get($id)) {
                return [
                    'id' => $announ->id,
                    'type_object' => $announ->type_object,
                    'deal_type' => $announ->deal_type,
                    'rental_type' => $announ->rental_type,
                    'available' => false,
                    'unavailable_reason' => $announ->sold_at ? ($announ->deal_type === 'Аренда' ? 'Уже сдано' : 'Уже продано')
                        : ($announ->deleted_at ? 'Снято с публикации' : 'Временно недоступно'),
                ];
            }
            return null;
        })->filter()->values();
    }
}
