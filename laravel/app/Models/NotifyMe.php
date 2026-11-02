<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifyMe extends Model
{
    protected $fillable = [
        'user_id',
        'filters',
    ];

    protected $casts = ['filters' => 'array'];

    use HasFactory;
}
