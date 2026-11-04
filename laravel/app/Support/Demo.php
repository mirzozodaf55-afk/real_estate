<?php

namespace App\Support;

/**
 * Публичное демо (DEMO_MODE=true): посетитель входит одной кнопкой под готовым аккаунтом,
 * данные возвращаются к исходным командой demo:reset (при запуске контейнера и каждую ночь).
 */
class Demo
{
    /** Роль на кнопке => почта демо-аккаунта (создаёт Database\Seeders\DemoSeeder) */
    public const ACCOUNTS = [
        'buyer' => 'buyer@demo.domly',
        'owner' => 'owner@demo.domly',
        'moderator' => 'moderator@demo.domly',
        'admin' => 'admin@demo.domly',
    ];

    public static function enabled(): bool
    {
        return (bool) config('app.demo');
    }
}
