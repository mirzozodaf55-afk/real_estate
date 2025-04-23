<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conditions extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function condition(){
        return $this->hasMany(ConditionAnnoun::class);
    }
}