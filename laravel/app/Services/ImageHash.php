<?php

namespace App\Services;

use Intervention\Image\Facades\Image;

/**
 * Перцептивный хеш фото (pHash): 64 бита, которые почти не меняются при сжатии, уменьшении и лёгкой правке снимка.
 * Два фото похожи, если хеши отличаются меньше чем в ~10 битах из 64 (расстояние Хэмминга).
 * Алгоритм: 32×32 в оттенках серого → двумерное ДКП → левый верхний угол 8×8 (низкие частоты) → бит = выше медианы.
 */
class ImageHash
{
    public const SIMILAR_DISTANCE = 10;
    private const SIZE = 32;

    public static function phash(string $path): ?string
    {
        try {
            $img = Image::make($path)->greyscale()->resize(self::SIZE, self::SIZE);
        } catch (\Throwable $e) {
            return null;
        }

        $pixels = [];
        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                $pixels[$y][$x] = $img->pickColor($x, $y)[0]; // в сером R = G = B
            }
        }

        // ДКП по строкам, потом по столбцам; нужны только первые 8 коэффициентов в каждую сторону
        $cos = [];
        for ($u = 0; $u < 8; $u++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                $cos[$u][$x] = cos((2 * $x + 1) * $u * M_PI / (2 * self::SIZE));
            }
        }
        $rows = [];
        for ($y = 0; $y < self::SIZE; $y++) {
            for ($u = 0; $u < 8; $u++) {
                $sum = 0.0;
                for ($x = 0; $x < self::SIZE; $x++) {
                    $sum += $pixels[$y][$x] * $cos[$u][$x];
                }
                $rows[$y][$u] = $sum;
            }
        }
        $coeffs = [];
        for ($v = 0; $v < 8; $v++) {
            for ($u = 0; $u < 8; $u++) {
                $sum = 0.0;
                for ($y = 0; $y < self::SIZE; $y++) {
                    $sum += $rows[$y][$u] * $cos[$v][$y];
                }
                $coeffs[] = $sum;
            }
        }

        // самый первый коэффициент (средняя яркость) в медиану не берём — он сильно больше остальных
        $sorted = array_slice($coeffs, 1);
        sort($sorted);
        $median = $sorted[intdiv(count($sorted), 2)];

        $hex = '';
        foreach (array_chunk($coeffs, 4) as $nibble) {
            $n = 0;
            foreach ($nibble as $c) {
                $n = ($n << 1) | ($c > $median ? 1 : 0);
            }
            $hex .= dechex($n);
        }
        return $hex;
    }

    public static function distance(string $a, string $b): int
    {
        $d = 0;
        for ($i = 0; $i < 16; $i++) {
            $d += substr_count(decbin(hexdec($a[$i]) ^ hexdec($b[$i])), '1');
        }
        return $d;
    }
}
