<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoTimeUpdate implements ShouldBroadcast
{
    public $time;

    public function __construct($time)
    {
        $this->time = $time;
    }

    public function broadcastOn()
    {
        return new Channel('video-sync');
    }

    public function broadcastWith()
    {
        return ['time' => $this->time];
    }
}

