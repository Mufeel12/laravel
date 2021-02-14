<?php

namespace Tests\Feature;

use App\Notifications\ProcessingCompleted;
use App\User;
use App\Video;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @group notifications
     */
    public function testSendingNotification()
    {
        $user = Auth::loginUsingId(1);
        $video = Video::find(50);
        #$res = $user->notify(new ProcessingCompleted($video));
        $user = User::find($video->owner);
        $user->notify(new ProcessingCompleted($video));
        $this->assertTrue(true);
    }
}
