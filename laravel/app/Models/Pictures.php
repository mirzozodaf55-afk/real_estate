<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pictures extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['pictures','position','rotation','announ_id'];
}
