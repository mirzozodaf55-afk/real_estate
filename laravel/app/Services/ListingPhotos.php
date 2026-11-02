<?php

namespace App\Services;

use App\Models\Pictures;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

/**
 * Фото объявлений: нарезка в 4 размера (public/{large,medium,small,xsmall}) и удаление файлов.
 * Общая для «Моих объявлений» и публикации через ИИ-помощника.
 */
class ListingPhotos
{
    public const SIZES = ['large' => [2250, 950], 'medium' => [1400, 800], 'small' => [700, 400], 'xsmall' => [350, 200]];
    public const MAX = 20;

    /** Сохраняет файл во всех размерах и привязывает к объявлению. Возвращает имя файла. */
    public static function store(UploadedFile $file, int $announId, int $position, int $rotation = 0): string
    {
        $name = uniqid() . '.' . $file->getClientOriginalExtension();
        foreach (self::SIZES as $folder => [$w, $h]) {
            Image::make($file)->fit($w, $h)->save(public_path("$folder/$name"));
        }
        Pictures::create([
            'pictures' => $name,
            'position' => $position,
            'rotation' => $rotation % 360,
            'announ_id' => $announId,
        ]);
        return $name;
    }

    public static function deleteFiles(?string $name): void
    {
        if (!$name) {
            return;
        }
        foreach (array_keys(self::SIZES) as $folder) {
            $path = public_path("$folder/" . basename($name));
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }
}
