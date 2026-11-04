<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Готовая причина отказа: title — для модератора в списке, text — что получит владелец; *_en — то же по-английски */
class RejectionReason extends Model
{
    protected $fillable = ['title', 'text', 'title_en', 'text_en', 'sort', 'active'];
    protected $casts = ['active' => 'boolean'];
}
