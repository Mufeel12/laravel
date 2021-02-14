<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendShardedSnapEmail extends Notification
{
    use Queueable;

    public $mailTo;
    public $sharedSnap;
    public $video;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($mailTo, $sharedSnap, $video, $user)
    {
        $this->mailTo =  $mailTo;
        $this->sharedSnap = $sharedSnap;
        $this->video = $video;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if($this->mailTo == 'Creator'){
            return (new MailMessage)
                        ->from(config('env.MAIL_FROM'))
                        ->subject($this->user->name.' your snap is ready to watch!')
                        ->line('Hello '.$this->user->name )
                        ->line('The new snap “'.$this->video->title.'” you created for '.$this->sharedSnap->creator_name.' is ready to watch')
                        ->line(new HtmlString('<a href="'.config('env.ROOT_URL').'/watch/'.$this->video->video_id.'">Watch it here</a>'))
                        ->line('Thank you for using SnapByte by Adilo.');
        }else{
           return (new MailMessage)
                        ->from(config('env.MAIL_FROM'))
                        ->subject($this->user->name.' Sent You A New Snap')
                        ->line('Hello '.$this->sharedSnap->creator_name )
                        ->line($this->user->name.' has sent you a new snap “'.$this->video->title.'”')
                        ->line(new HtmlString('<a href="'.config('env.ROOT_URL').'/watch/'.$this->video->video_id.'">Watch it here</a>'))
                        ->line(new HtmlString('<a href="'.config('env.ROOT_URL').'/snaps">Manage your shared snaps now</a>'))
                        ->line('Thank you for using Bigcommand!'); 
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
