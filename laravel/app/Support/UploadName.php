<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Имя для сохранения загруженного файла в public/. Расширение — по содержимому файла и только из разрешённых:
 * расширение из имени, присланного клиентом, брать нельзя — видео «x.php» сохранилось бы как исполняемый .php.
 */
class UploadName
{
    public const IMAGES = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    public const VIDEOS = ['mp4', 'mov', 'avi', 'webm', '3gp', 'mkv'];

    public static function make(UploadedFile $file, array $allowed, string $fallback): string
    {
        $ext = strtolower((string) $file->guessExtension());
        return uniqid() . '.' . (in_array($ext, $allowed, true) ? $ext : $fallback);
    }
}
