<?php

namespace App\Http\Controllers;

use App\Support\UploadName;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class getImageController extends Controller
{
    public function getAvatar(Request $request){
        $request->validate(['avatar' => 'required|file|image|max:10240'], [
            'avatar.*' => 'Загрузите фото (JPG, PNG, WebP) до 10 МБ',
        ]);
        $file = $request->file('avatar');
        $image = Image::make($file);
        $name = UploadName::make($file, UploadName::IMAGES, 'jpg');
        $image->fit(700, 400)->save(public_path('avatar/' . $name));

        // Аватар меняем только себе — пользователь из токена, а не из присланного phone
        $owner = $request->user();
        $owner->update(['avatar'=>$name]);

        return $owner;
    }
}
