<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\AnnounNotification;
use App\Models\ConditionAnnoun;
use App\Models\InfrastructureAnnoun;
use App\Models\Likes;
use App\Models\Pictures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ListingPhotos;

/**
 * "Мои объявления": список, редактирование, снятие с публикации, возврат и удаление.
 * Каждое действие работает только с объявлениями текущего пользователя
 * (старые /delete-announ, /restore, /get-data-info принимали любой id из запроса).
 */
class MyAnnounController extends Controller
{
    private const MAX_PHOTOS = ListingPhotos::MAX;

    /** Поле формы => колонка в БД и правило валидации */
    private const EDITABLE = [
        'title' => ['title', 'nullable|string|max:150'],
        'description' => ['description', 'nullable|string|max:5000'],
        'price' => ['price', 'required|integer|min:1|max:2000000000'],
        'quantityRoom' => ['quantity_room', 'nullable|integer|min:0|max:50'],
        'totalArea' => ['total_area', 'nullable|integer|min:0|max:100000'],
        'livingArea' => ['living_area', 'nullable|integer|min:0|max:100000'],
        'kitchenArea' => ['kitchen_area', 'nullable|integer|min:0|max:10000'],
        'floor' => ['floor', 'nullable|integer|min:-5|max:300'],
        'floorInHouse' => ['floor_in_house', 'nullable|integer|min:0|max:300'],
        'year' => ['year_of_construction', 'nullable|integer|min:1800|max:2100'],
        'houseType' => ['house_type', 'nullable|string|max:60'],
        'repair' => ['repair', 'nullable|string|max:60'],
        'balcony' => ['Balcony', 'nullable|string|max:20'],
        'elevator' => ['elevator', 'nullable|string|max:20'],
        'parking' => ['parking', 'nullable|string|max:60'],
        'houseArea' => ['house_area', 'nullable|integer|min:0|max:100000'],
        'landArea' => ['land_area', 'nullable|integer|min:0|max:1000000'],
        'bedrooms' => ['quantity_bedrooms', 'nullable|integer|min:0|max:50'],
        'conditionHome' => ['condition_of_the_home', 'nullable|string|max:60'],
        'electricity' => ['electricity', 'nullable|string|max:20'],
        'landCategory' => ['Land_category', 'nullable|string|max:60'],
        'buildingArea' => ['building_area', 'nullable|integer|min:0|max:1000000'],
        'ceilingHeight' => ['CeilingHeight', 'nullable|integer|min:0|max:1000'],
        'layout' => ['layout', 'nullable|string|max:60'],
        'state' => ['state', 'nullable|string|max:60'],
        'furniture' => ['furniture', 'nullable|string|max:60'],
        'numberSeats' => ['numberSeats', 'nullable|integer|min:0|max:100000'],
        'rentalPeriod' => ['rental_period', 'nullable|string|max:40'],
        'allowKids' => ['allowKids', 'nullable|string|max:40'],
        'allowAnimal' => ['allowAnimal', 'nullable|string|max:40'],
        'linkVideo' => ['link_video', 'nullable|url|max:500'],
    ];

    public function index(Request $request)
    {
        $announs = Announ::anyStatus()->withTrashed()
            ->where('owner_id', $request->user()->id)
            ->with('pictures:id,announ_id,pictures,rotation,position')
            ->withCount('likes')
            ->latest()
            ->get();

        return response()->json(['data' => $announs]);
    }

    public function show(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);
        $announ->load('pictures:id,announ_id,pictures,rotation,position');
        $data = $announ->toArray();
        $data['condition_ids'] = ConditionAnnoun::where('announ_id', $announ->id)->pluck('condition_id');
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);

        $fields = json_decode((string) $request->input('announ'), true);
        $keep = json_decode((string) $request->input('photos'), true);
        $newMeta = json_decode((string) $request->input('newPhotosMeta'), true) ?: [];
        if (!is_array($fields) || !is_array($keep)) {
            return response()->json(['message' => 'Некорректный формат данных'], 422);
        }
        // поля приходят внутри JSON, поэтому ConvertEmptyStringsToNull их не обрабатывает — делаем сами
        $fields = array_map(fn ($v) => (is_string($v) && trim($v) === '') ? null : $v, $fields);

        $validator = validator($fields, array_map(fn ($def) => $def[1], self::EDITABLE) + [
            'conditions' => 'nullable|array',
            'conditions.*' => 'integer',
        ]);
        $request->validate([
            'newPhotos' => 'nullable|array',
            'newPhotos.*' => 'file|image|max:20480',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Проверьте поля объявления', 'errors' => $validator->errors()], 422);
        }

        $ownPictures = Pictures::where('announ_id', $announ->id)->get()->keyBy('id');
        $keep = array_values(array_filter($keep, fn ($p) => isset($p['id']) && $ownPictures->has($p['id'])));
        $newFiles = $request->file('newPhotos', []);

        $total = count($keep) + count($newFiles);
        if ($total < 1) {
            return response()->json(['message' => 'Нужна хотя бы одна фотография'], 422);
        }
        if ($total > self::MAX_PHOTOS) {
            return response()->json(['message' => 'Не больше ' . self::MAX_PHOTOS . ' фотографий'], 422);
        }

        $saved = [];
        DB::transaction(function () use ($announ, $fields, $keep, $newFiles, $newMeta, $ownPictures, &$saved) {
            $update = [];
            foreach (self::EDITABLE as $input => [$column]) {
                if (array_key_exists($input, $fields)) {
                    $value = $fields[$input];
                    $update[$column] = ($value === '' ? null : $value);
                }
            }
            // После правки объявление снова проверяет модератор — иначе одобрив безобидный текст,
            // можно было бы потом заменить его чем угодно. Отклонённое так же уходит на повторную проверку.
            $announ->forceFill($update + [
                'moderation_status' => Announ::PENDING,
                'moderation_reason' => null,
            ])->save();

            // Фото, которые пользователь убрал, удаляем вместе с файлами
            $keepIds = array_column($keep, 'id');
            foreach ($ownPictures as $picture) {
                if (!in_array($picture->id, $keepIds, true)) {
                    $this->deleteImageFiles($picture->pictures);
                    $picture->forceDelete();
                }
            }

            // Порядок (первое фото — обложка) и поворот оставшихся
            $position = 0;
            foreach ($keep as $item) {
                $ownPictures[$item['id']]->update([
                    'position' => $position++,
                    'rotation' => (int) ($item['rotation'] ?? 0) % 360,
                ]);
            }

            foreach ($newFiles as $index => $file) {
                $saved[] = ListingPhotos::store($file, $announ->id, $position++, (int) ($newMeta[$index]['rotation'] ?? 0));
            }

            if (array_key_exists('conditions', $fields)) {
                ConditionAnnoun::where('announ_id', $announ->id)->delete();
                foreach (array_unique((array) $fields['conditions']) as $conditionId) {
                    ConditionAnnoun::create(['condition_id' => (int) $conditionId, 'announ_id' => $announ->id]);
                }
            }
        });

        // фото и текст могли поменяться — ИИ проверит заново, пока объявление ждёт модератора
        \App\Services\ListingChecks::dispatch($announ->id); // pHash фото, ИИ-проверка фото и текста/цены → риск в очереди

        $announ->refresh()->load('pictures:id,announ_id,pictures,rotation,position');
        return response()->json(['message' => 'Объявление сохранено и отправлено на проверку', 'data' => $announ]);
    }

    /** Снять с публикации (мягкое удаление — можно вернуть) */
    public function unpublish(Request $request, int $id)
    {
        $this->findOwn($request, $id)->delete();
        return response()->json(['message' => 'Объявление снято с публикации']);
    }

    public function publish(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);
        $announ->restore();
        return response()->json(['message' => $announ->moderation_status === Announ::APPROVED
            ? 'Объявление снова опубликовано'
            : 'Объявление вернётся в поиск после проверки модератором']);
    }

    /** «Продано» / «Сдано»: из поиска убираем, у владельца остаётся — можно вернуть, если сделка сорвалась */
    public function markSold(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);
        $announ->forceFill(['sold_at' => now()])->save();
        return response()->json([
            'message' => $announ->deal_type === 'Аренда' ? 'Отмечено как сданное — объявление убрано из поиска' : 'Отмечено как проданное — объявление убрано из поиска',
            'sold_at' => $announ->sold_at,
        ]);
    }

    public function unmarkSold(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);
        $announ->forceFill(['sold_at' => null])->save();
        return response()->json(['message' => $announ->moderation_status === Announ::APPROVED
            ? 'Объявление снова в поиске'
            : 'Объявление вернётся в поиск после проверки модератором']);
    }

    /** Удалить навсегда — со всеми фото, удобствами, лайками и уведомлениями */
    public function destroy(Request $request, int $id)
    {
        $announ = $this->findOwn($request, $id);

        DB::transaction(function () use ($announ) {
            foreach (Pictures::where('announ_id', $announ->id)->get() as $picture) {
                $this->deleteImageFiles($picture->pictures);
                $picture->forceDelete();
            }
            ConditionAnnoun::where('announ_id', $announ->id)->delete();
            InfrastructureAnnoun::where('announ_id', $announ->id)->delete();
            Likes::where('announ_id', $announ->id)->delete();
            AnnounNotification::where('announ_id', $announ->id)->delete();
            $announ->forceDelete();
        });

        return response()->json(['message' => 'Объявление удалено']);
    }

    private function findOwn(Request $request, int $id): Announ
    {
        return Announ::anyStatus()->withTrashed()
            ->where('owner_id', $request->user()->id)
            ->findOrFail($id);
    }

    private function deleteImageFiles(?string $name): void
    {
        ListingPhotos::deleteFiles($name);
    }
}
