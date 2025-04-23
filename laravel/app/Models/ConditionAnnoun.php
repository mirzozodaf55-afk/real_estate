<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConditionAnnoun extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=['condition_id','announ_id'];
    // public function condition(){
    //     return $this->hasOne(Announ::class, 'id', 'announ_id');
    // }
}