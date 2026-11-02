<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Готовая причина отказа: title — для модератора в списке, text — что получит владелец */
class RejectionReason extends Model
{
    protected $fillable = ['title', 'text', 'sort', 'active'];
    protected $casts = ['active' => 'boolean'];
}
