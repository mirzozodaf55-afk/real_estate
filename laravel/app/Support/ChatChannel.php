<?php

namespace App\Support;

/**
 * Канал websocket одной переписки. Раньше все сообщения шли в общий канал "your-channel"
 * и приходили каждому открытому чату (в чужих переписках появлялись чужие сообщения).
 */
class ChatChannel
{
    public static function name(int $a, int $b): string
    {
        return 'chat.' . min($a, $b) . '.' . max($a, $b);
    }
}
