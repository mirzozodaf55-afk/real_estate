<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DeletedAccount extends Model
{
    /** Сколько после удаления нельзя завести аккаунт на ту же почту */
    public const COOLDOWN_DAYS = 30;

    protected $fillable = ['email_hash', 'blocked_until'];
    protected $casts = ['blocked_until' => 'datetime'];

    public static function hashEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }

    public static function remember(string $email): void
    {
        self::create(['email_hash' => self::hashEmail($email), 'blocked_until' => now()->addDays(self::COOLDOWN_DAYS)]);
    }

    /** До какого момента почта занята недавно удалённым аккаунтом (null — свободна) */
    public static function blockedUntil(string $email): ?Carbon
    {
        $until = self::where('email_hash', self::hashEmail($email))->where('blocked_until', '>', now())->max('blocked_until');
        return $until ? Carbon::parse($until) : null;
    }

    public static function message(Carbon $until): string
    {
        return 'Аккаунт с этой почтой был удалён. Создать новый можно с ' . $until->format('d.m.Y') . '.';
    }
}
