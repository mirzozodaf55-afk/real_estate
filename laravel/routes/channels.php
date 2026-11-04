<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Кто может подписаться на приватный канал websocket. Сайт спрашивает POST /api/broadcasting/auth
| (BroadcastServiceProvider) с токеном пользователя; false — подписка отклоняется.
|
*/

// колокольчик и «новое сообщение» — только самому пользователю (App\Events\NotifyMe, NewChatMessage)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// переписка — только двум её участникам (App\Support\ChatChannel: chat.{меньший id}.{больший id})
Broadcast::channel('chat.{a}.{b}', function ($user, $a, $b) {
    return in_array((int) $user->id, [(int) $a, (int) $b], true);
});
