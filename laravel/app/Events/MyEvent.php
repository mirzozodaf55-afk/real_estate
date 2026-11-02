<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        // только участникам этой переписки, а не всем открытым чатам
        return new \Illuminate\Broadcasting\Channel(\App\Support\ChatChannel::name((int) $this->data['user_id'], (int) $this->data['friend_id']));
    }

    public function broadcastAs()
    {
        return "MyEvent";
    }
}
