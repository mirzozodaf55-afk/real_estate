<?php

namespace App\Services;

use App\Models\ConditionAnnoun;
use Illuminate\Database\Eloquent\Builder;

/**
 * Единые правила фильтрации объявлений.
 * Используются и страницей списка (поиск), и подписками «Уведомить меня» —
 * чтобы подписка срабатывала ровно на те объявления, которые пользователь видел бы в поиске.
 */
class AnnounFilter
{
    /** Ключи, которые имеют смысл как фильтр (всё остальное — page, sort и т.п. — в подписку не попадает) */
    public const KEYS = [
        'dealType', 'typeRealEstate', 'typeObject', 'buildingType', 'quantityRoom',
        'priceFrom', 'priceTo', 'min_price', 'max_price',
        'totalAreaFrom', 'totalAreaTo', 'areaFrom', 'areaTo', 'kitchenAreaFrom', 'kitchenAreaTo',
        'floorFrom', 'floorTo', 'floorHouseFrom', 'floorHouseTo', 'yearFrom', 'yearTo',
        'landAreaFrom', 'landAreaTo', 'houseAreaFrom', 'houseAreaTo', 'typeHome', 'repair', 'parking', 'balcon', 'condition', 'q',
    ];

    private const ARRAY_KEYS = ['buildingType', 'quantityRoom', 'typeHome', 'repair', 'parking', 'condition'];

    public static function apply(Builder $query, array $f): Builder
    {
        // Тип сделки. "Посуточно" — аренда с rental_type = Посуточно; обычная аренда посуточные не показывает.
        $dealType = $f['dealType'] ?? null;
        if ($dealType === 'Посуточно') {
            $query->where('deal_type', 'Аренда')->where('rental_type', 'Посуточно');
        } elseif ($dealType === 'Аренда') {
            $query->where('deal_type', 'Аренда')
                ->where(fn ($q) => $q->whereNull('rental_type')->orWhere('rental_type', '!=', 'Посуточно'));
        } elseif ($dealType) {
            $query->where('deal_type', $dealType);
        }

        if (!empty($f['typeRealEstate'])) {
            $query->where('type_real_estate', $f['typeRealEstate']);
        }

        // buildingType — список типов (например, "Квартира" + "Квартира в Новостройке"), typeObject — один тип
        $buildingType = self::list($f['buildingType'] ?? null);
        if ($buildingType) {
            $query->whereIn('type_object', $buildingType);
        } elseif (!empty($f['typeObject'])) {
            $query->where('type_object', $f['typeObject']);
        }

        // Комнатность: "4" означает "4 и больше"
        $rooms = array_map('intval', self::list($f['quantityRoom'] ?? null));
        if ($rooms) {
            $exact = array_values(array_filter($rooms, fn ($r) => $r < 4));
            $fourPlus = max($rooms) >= 4;
            $query->where(function ($q) use ($exact, $fourPlus) {
                if ($exact) {
                    $q->whereIn('quantity_room', $exact);
                }
                if ($fourPlus) {
                    $q->orWhere('quantity_room', '>=', 4);
                }
            });
        }

        // Цена (старые имена min_price/max_price тоже принимаем)
        self::range($query, 'price', $f['priceFrom'] ?? $f['min_price'] ?? null, $f['priceTo'] ?? $f['max_price'] ?? null);
        self::range($query, 'total_area', $f['totalAreaFrom'] ?? null, $f['totalAreaTo'] ?? null);
        self::range($query, 'living_area', $f['areaFrom'] ?? null, $f['areaTo'] ?? null);
        self::range($query, 'kitchen_area', $f['kitchenAreaFrom'] ?? null, $f['kitchenAreaTo'] ?? null);
        self::range($query, 'floor', $f['floorFrom'] ?? null, $f['floorTo'] ?? null);
        self::range($query, 'floor_in_house', $f['floorHouseFrom'] ?? null, $f['floorHouseTo'] ?? null);
        self::range($query, 'year_of_construction', $f['yearFrom'] ?? null, $f['yearTo'] ?? null);
        self::range($query, 'land_area', $f['landAreaFrom'] ?? null, $f['landAreaTo'] ?? null);
        self::range($query, 'house_area', $f['houseAreaFrom'] ?? null, $f['houseAreaTo'] ?? null);

        foreach (['typeHome' => 'house_type', 'repair' => 'repair', 'parking' => 'parking'] as $param => $column) {
            $values = self::list($f[$param] ?? null);
            if ($values) {
                $query->whereIn($column, $values);
            }
        }

        if (!empty($f['balcon'])) {
            $query->where('Balcony', $f['balcon']);
        }

        // Удобства — подзапросом, а не join: join размножал объявления на каждое совпавшее удобство
        $conditions = array_map('intval', self::list($f['condition'] ?? null));
        if ($conditions) {
            $query->whereIn('id', ConditionAnnoun::whereIn('condition_id', $conditions)->select('announ_id'));
        }

        if (!empty($f['q'])) {
            $like = '%' . trim($f['q']) . '%';
            // по адресу с карты тоже: «Сино», «Худжанд», «Рудаки»
            $query->where(fn ($sub) => $sub->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhere('address', 'like', $like));
        }

        return $query;
    }

    /**
     * Оставляет только значимые фильтры в стабильном виде (без пустых значений, массивы отсортированы),
     * чтобы одинаковые подписки можно было распознать как дубли.
     */
    public static function normalize(array $f): array
    {
        $result = [];
        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $f)) {
                continue;
            }
            $value = $f[$key];
            // из URL одиночное значение списка приходит строкой ("1"), а не массивом (["1"])
            if (in_array($key, self::ARRAY_KEYS, true) && !is_array($value)) {
                $value = ($value === null || $value === '') ? [] : [$value];
            }
            if (is_array($value)) {
                $value = self::list($value);
                sort($value);
                if ($value) {
                    $result[$key] = array_map('strval', $value);
                }
            } elseif ($value !== null && $value !== '') {
                $result[$key] = (string) $value;
            }
        }
        ksort($result);
        return $result;
    }

    /** Описание сохранённого поиска для писем: «Снять · Квартира · 1, 2 комн. · до 5 000 сомони» */
    public static function describe(array $f): string
    {
        $deals = ['Продажа' => 'Купить', 'Аренда' => 'Снять', 'Посуточно' => 'Посуточно'];
        $parts = [];
        if (!empty($f['dealType'])) {
            $parts[] = $deals[$f['dealType']] ?? $f['dealType'];
        }
        if (!empty($f['typeObject'])) {
            $parts[] = $f['typeObject'];
        } elseif ($types = self::list($f['buildingType'] ?? null)) {
            $parts[] = implode(', ', $types);
        }
        if ($rooms = self::list($f['quantityRoom'] ?? null)) {
            sort($rooms);
            $parts[] = implode(', ', array_map(fn ($r) => (string) $r === '4' ? '4+' : $r, $rooms)) . ' комн.';
        }
        $fmt = fn ($n) => number_format((int) $n, 0, '', ' ');
        $from = $f['priceFrom'] ?? $f['min_price'] ?? null;
        $to = $f['priceTo'] ?? $f['max_price'] ?? null;
        if (is_numeric($from) && is_numeric($to)) {
            $parts[] = $fmt($from) . '–' . $fmt($to) . ' сомони';
        } elseif (is_numeric($from)) {
            $parts[] = 'от ' . $fmt($from) . ' сомони';
        } elseif (is_numeric($to)) {
            $parts[] = 'до ' . $fmt($to) . ' сомони';
        }
        if (!empty($f['q'])) {
            $parts[] = '«' . $f['q'] . '»';
        }
        return $parts ? implode(' · ', $parts) : 'Все объявления';
    }

    private static function list($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        return array_values(array_filter((array) $value, fn ($v) => $v !== null && $v !== ''));
    }

    private static function range(Builder $query, string $column, $from, $to): void
    {
        if (is_numeric($from)) {
            $query->where($column, '>=', $from);
        }
        if (is_numeric($to)) {
            $query->where($column, '<=', $to);
        }
    }
}
