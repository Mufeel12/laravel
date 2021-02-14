<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class Comment extends Notification
{
    use Queueable;

    protected $video;

    protected $sender;

    protected $receiver;

    protected $comment;

    /**
     * Create a new notification instance.
     *
     * @param $video
     * @param $sender
     * @param $receiver
     */
    public function __construct($video, $sender, $receiver, $comment)
    {
        $this->video = $video;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', SparkChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    { info($notifiable->email);
        return (new MailMessage)
                    ->from('notifications@bigcommand.com', 'Bigcommand Notifications')
                    ->subject('New comment from ' . $this->sender->name)
                    ->line($this->sender->name . ' commented on ' . $this->video->title)
                    ->action('View Video', url('/projects/' . $this->video->project . '/edit/' . $this->video->id))
                    ->line('Thank you for using Adilo!');
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

    public function toSpark($notifiable)
    {
        return (new SparkNotification)
            ->from($this->sender)
            ->action('View Comment', url('/projects/' . $this->video->project . '/edit/' . $this->video->id ))# . '/comments/' . $this->comment->id))
            ->icon('fa-comment')
            ->body($this->comment->body);
    }
}
