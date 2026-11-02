<?php

namespace App\Http\Controllers;

use App\Models\ModerationLog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

/**
 * Оформление сайта: фон главного блока на главной странице.
 * Читать может любой посетитель, менять — только администратор.
 */
class SiteSettingsController extends Controller
{
    private const DEFAULT_OVERLAY = 55; // % затемнения поверх фото — чтобы белый текст читался на любом снимке

    public function index()
    {
        $hero = SiteSetting::get('hero_image');
        return response()->json([
            'hero_image' => $hero ? url('site/' . $hero) : null,
            'hero_overlay' => (int) SiteSetting::get('hero_overlay', self::DEFAULT_OVERLAY),
        ]);
    }

    public function updateHero(Request $request)
    {
        $data = $request->validate([
            'image' => 'nullable|file|image|max:20480',
            'overlay' => 'nullable|integer|min:0|max:85',
        ], [
            'image.image' => 'Нужна фотография (JPG, PNG, WEBP)',
            'image.max' => 'Фото должно быть не больше 20 МБ',
        ]);

        if ($request->hasFile('image')) {
            File::ensureDirectoryExists(public_path('site'));
            $name = 'hero-' . uniqid() . '.jpg';
            // до 2400 px по ширине: чётко на больших экранах, но страница не грузит 10-мегабайтный оригинал
            $image = Image::make($request->file('image'));
            // поворот снимков с телефона по EXIF — только если в PHP включено расширение exif
            if (function_exists('exif_read_data')) {
                $image->orientate();
            }
            $image->resize(2400, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                })
                ->encode('jpg', 82)
                ->save(public_path('site/' . $name));
            $this->deleteHeroFile();
            SiteSetting::put('hero_image', $name);
        }
        if (array_key_exists('overlay', $data) && $data['overlay'] !== null) {
            SiteSetting::put('hero_overlay', $data['overlay']);
        }

        $this->log($request, 'hero_update');
        return $this->index();
    }

    public function deleteHero(Request $request)
    {
        $this->deleteHeroFile();
        SiteSetting::put('hero_image', null);
        $this->log($request, 'hero_delete');
        return $this->index();
    }

    private function deleteHeroFile(): void
    {
        $old = SiteSetting::get('hero_image');
        if ($old && File::exists(public_path('site/' . basename($old)))) {
            File::delete(public_path('site/' . basename($old)));
        }
    }

    private function log(Request $request, string $action): void
    {
        ModerationLog::create(['moderator_id' => $request->user()->id, 'action' => $action]);
    }
}
