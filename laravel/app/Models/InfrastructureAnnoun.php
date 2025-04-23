<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfrastructureAnnoun extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['infrastructure_id','announ_id'];
}
