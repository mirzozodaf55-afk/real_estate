<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Уведомление получателю о новом сообщении в чате (счётчик на иконке сообщений + всплывашка) */
class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $recipientId, public array $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('user.' . $this->recipientId)];
    }

    public function broadcastAs(): string
    {
        return 'chat-message';
    }
}
