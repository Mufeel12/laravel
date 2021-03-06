<?php

namespace App\Listeners;

use Notification;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SendShardedSnapEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSharedSnapRecorder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $sharedSnap = $event->sharedSnap;
        $video = $event->video;
        $user = $event->user;
        Notification::route('mail', $sharedSnap->creator_email)->notify(new SendShardedSnapEmail('Recorder', $sharedSnap, $video, $user));

    }
}
