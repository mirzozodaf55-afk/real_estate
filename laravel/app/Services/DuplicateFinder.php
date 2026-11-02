<?php

namespace App\Services;

use App\Models\Announ;
use App\Models\Pictures;
use Illuminate\Support\Facades\DB;

/**
 * Возможные дубликаты объявления для модератора:
 *  - фото: тот же снимок (pHash отличается меньше чем на ImageHash::SIMILAR_DISTANCE бит) в другом объявлении;
 *  - текст: почти одинаковые заголовок или описание у объявления того же типа.
 */
class DuplicateFinder
{
    private const TEXT_CANDIDATES = 1000;
    private const DESCRIPTION_SIMILARITY = 0.6; // доля общих троек слов (Жаккар)
    private const TITLE_SIMILARITY = 90;        // % по similar_text

    /** Посчитать pHash для фото объявления, у которых его ещё нет (файлы — public/small) */
    public static function hashPhotos(Announ $announ): void
    {
        foreach (Pictures::where('announ_id', $announ->id)->whereNull('phash')->get(['id', 'pictures']) as $picture) {
            $path = public_path('small/' . basename($picture->pictures));
            if (is_file($path) && ($hash = ImageHash::phash($path))) {
                $picture->forceFill(['phash' => $hash])->saveQuietly();
            }
        }
    }

    public static function find(Announ $announ): array
    {
        return ['photos' => self::byPhotos($announ), 'text' => self::byText($announ)];
    }

    private static function byPhotos(Announ $announ): array
    {
        $own = Pictures::where('announ_id', $announ->id)->whereNotNull('phash')->pluck('phash')->unique();
        $matches = [];
        foreach ($own as $hash) {
            // расстояние Хэмминга прямо в MySQL: XOR двух 64-битных чисел и подсчёт единиц
            $rows = Pictures::where('announ_id', '!=', $announ->id)->whereNotNull('phash')
                ->select('announ_id', DB::raw('MIN(BIT_COUNT(CAST(CONV(phash, 16, 10) AS UNSIGNED) ^ CAST(CONV(?, 16, 10) AS UNSIGNED))) as distance'))
                ->addBinding($hash, 'select')
                ->groupBy('announ_id')
                ->havingRaw('distance <= ?', [ImageHash::SIMILAR_DISTANCE])
                ->get();
            foreach ($rows as $row) {
                $matches[$row->announ_id] = min($matches[$row->announ_id] ?? 64, (int) $row->distance);
            }
        }
        return self::describe($matches, fn ($d) => $d === 0 ? 'то же фото' : 'очень похожее фото', true);
    }

    private static function byText(Announ $announ): array
    {
        $title = self::normalize($announ->title);
        $shingles = self::shingles($announ->description);
        if (mb_strlen($title) < 10 && count($shingles) < 5) {
            return [];
        }

        $candidates = Announ::anyStatus()->withTrashed()
            ->where('id', '!=', $announ->id)
            ->where('type_object', $announ->type_object)
            ->orderByDesc('id')->limit(self::TEXT_CANDIDATES)
            ->get(['id', 'title', 'description']);

        $matches = [];
        foreach ($candidates as $c) {
            $score = 0;
            if (mb_strlen($title) >= 10) {
                similar_text($title, self::normalize($c->title), $percent);
                if ($percent >= self::TITLE_SIMILARITY) {
                    $score = max($score, $percent / 100);
                }
            }
            if (count($shingles) >= 5) {
                $other = self::shingles($c->description);
                $union = count(array_unique(array_merge($shingles, $other)));
                $jaccard = $union ? count(array_intersect($shingles, $other)) / $union : 0;
                if ($jaccard >= self::DESCRIPTION_SIMILARITY) {
                    $score = max($score, $jaccard);
                }
            }
            if ($score > 0) {
                $matches[$c->id] = $score;
            }
        }
        arsort($matches);
        return self::describe(array_slice($matches, 0, 5, true), fn ($s) => 'текст совпадает на ' . round($s * 100) . '%', false);
    }

    /** id → данные для карточки модератора: заголовок, статус, владелец (тот же или другой), чем похоже */
    private static function describe(array $matches, callable $label, bool $ascending): array
    {
        if (!$matches) {
            return [];
        }
        $ascending ? asort($matches) : arsort($matches);
        $announs = Announ::anyStatus()->withTrashed()->whereIn('id', array_keys($matches))
            ->get(['id', 'title', 'type_object', 'price', 'owner_id', 'moderation_status', 'sold_at', 'deleted_at'])->keyBy('id');

        return collect($matches)->map(function ($value, $id) use ($announs, $label) {
            $a = $announs->get($id);
            return $a ? [
                'id' => $a->id,
                'title' => $a->title ?: $a->type_object,
                'price' => $a->price,
                'owner_id' => $a->owner_id,
                'status' => $a->deleted_at ? 'withdrawn' : ($a->sold_at ? 'sold' : $a->moderation_status),
                'match' => $label($value),
            ] : null;
        })->filter()->values()->all();
    }

    private static function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Тройки подряд идущих слов — устойчивы к перестановке абзацев и мелким правкам */
    private static function shingles(?string $text): array
    {
        $words = array_values(array_filter(explode(' ', self::normalize($text)), fn ($w) => mb_strlen($w) > 1));
        $out = [];
        for ($i = 0; $i + 2 < count($words); $i++) {
            $out[] = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
        }
        return array_values(array_unique($out));
    }
}
