<?php

namespace App\Events;

use App\User;
use App\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SharedSnapRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $video;
    public $sharedSnap;
    public $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($sharedSnap, $video)
    {
        $this->sharedSnap  = $sharedSnap;
        $this->video    = $video;
        $this->user = User::find($video->owner);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
