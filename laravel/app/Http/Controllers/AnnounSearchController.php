<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Services\AnnounFilter;
use Illuminate\Http\Request;

/**
 * Поиск объявлений для страницы списка.
 * В отличие от старого /api/get_announs фото, владелец и лайки приходят внутри каждого объявления
 * (раньше фото шли отдельным массивом и после удаления дублей "съезжали" на чужие объявления).
 */
class AnnounSearchController extends Controller
{
    private const PER_PAGE = 20;

    public function search(Request $request)
    {
        $query = AnnounFilter::apply(Announ::query(), $request->all());

        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'area_desc' => $query->orderByDesc('total_area'),
            // по умолчанию платные выше: VIP, потом ТОП, потом остальные — каждые по дате
            default => $query->promotedFirst()->latest(),
        };

        $viewer = $request->user('sanctum');

        $page = $query
            ->with(['pictures:id,announ_id,pictures,rotation,position', 'owner:id,name,last_name,avatar,uniq_id,phone_number,email'])
            ->withCount('likes')
            ->paginate(min((int) $request->input('perPage', self::PER_PAGE), 50));

        $likedIds = $viewer
            ? $viewer->LikedAnnouns()->whereIn('announs.id', $page->pluck('id'))->pluck('announs.id')->all()
            : [];

        $items = $page->getCollection()->map(function (Announ $announ) use ($viewer, $likedIds) {
            $data = $announ->toArray();
            $data['is_liked'] = in_array($announ->id, $likedIds, true);
            // Контакты владельца — только залогиненным
            if ($announ->owner && !$viewer) {
                unset($data['owner']['phone_number'], $data['owner']['email']);
            }
            return $data;
        });

        return response()->json([
            'data' => $items,
            'total' => $page->total(),
            'page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /**
     * Одно объявление для его страницы (/show_announ-{id}).
     * Опубликованное видят все; на проверке, отклонённое, проданное или снятое — только владелец и команда сайта.
     */
    public function show(Request $request, int $id)
    {
        $announ = Announ::anyStatus()->withTrashed()
            ->with(['pictures:id,announ_id,pictures,rotation,position', 'owner:id,name,last_name,avatar,uniq_id,phone_number,email,blocked_at'])
            ->withCount('likes')
            ->find($id);

        $viewer = $request->user('sanctum');
        $isPublic = $announ && !$announ->trashed() && $announ->moderation_status === Announ::APPROVED
            && !$announ->sold_at && !$announ->owner?->blocked_at;
        $canSeeHidden = $announ && $viewer && ($viewer->id === $announ->owner_id || $viewer->hasRole('moderator', 'admin'));
        if (!$announ || (!$isPublic && !$canSeeHidden)) {
            return response()->json(['message' => 'Объявление не найдено или снято с публикации'], 404);
        }

        $data = $announ->toArray();
        $data['image'] = $data['pictures']; // страница объявления ждёт фото в поле image
        $data['is_liked'] = $viewer ? $viewer->LikedAnnouns()->where('announs.id', $announ->id)->exists() : false;
        $data['is_public'] = $isPublic; // владелец видит пометку, что объявление пока не в поиске
        unset($data['owner']['blocked_at'], $data['photo_check']);
        // Контакты владельца — только залогиненным
        if ($announ->owner && !$viewer) {
            unset($data['owner']['phone_number'], $data['owner']['email']);
        }
        return response()->json(['data' => $data]);
    }
}
