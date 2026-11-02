<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends User
{
    use HasFactory;

    // role нет в $fillable: роль меняет только админ (Admin\UserController) или команда user:role
    protected $fillable = ['phone_number','name','last_name','birth_date','avatar','email','verification_code','uniq_id','ip_adress','last_seen_at'];

    // Никогда не отдаём наружу код входа и служебные поля, в каком бы ответе API ни оказалась модель
    // block_reason видит только админка (makeVisible в Admin\UserController)
    protected $hidden = ['verification_code', 'pass', 'ip_adress', 'password', 'remember_token', 'is_admin', 'block_reason'];

    protected $casts = [
        'last_seen_at'=> 'datetime',
        'blocked_at' => 'datetime',
    ];

    public const ROLES = ['user', 'moderator', 'admin'];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    /** Текст для заблокированного при входе и в ответах API */
    public function blockedMessage(): string
    {
        return 'Аккаунт заблокирован администрацией' . ($this->block_reason ? ': ' . $this->block_reason : '');
    }
    public function LikedAnnouns(){
        return $this->belongsToMany(Announ::class,'likes','user_id','announ_id');
    }
}
