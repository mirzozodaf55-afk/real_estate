<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends User
{
    use HasFactory;

    protected $fillable = ['phone_number','name','last_name','birth_date','avatar','email','verification_code','is_admin','uniq_id','ip_adress','last_seen_at'];

    protected $casts = [
        'last_seen_at'=> 'datetime',
    ];
    public function LikedAnnouns(){
        return $this->belongsToMany(Announ::class,'likes','user_id','announ_id');
    }
}
