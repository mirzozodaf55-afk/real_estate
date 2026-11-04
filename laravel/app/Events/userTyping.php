<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class userTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

     public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // «печатает…» видит только собеседник, а не все открытые чаты
        if (!empty($this->data['friendId'])) {
            return [new PrivateChannel(\App\Support\ChatChannel::name((int) $this->data['userId'], (int) $this->data['friendId']))];
        }
        return []; // без собеседника — никому (раньше уходило в общий публичный канал)
    }
    
    public function broadcastAs()
    {
        return 'userTyping';
    }
}
