<?php

namespace App\Notifications;

use App\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class ProcessingCompleted extends Notification
{
    use Queueable;

    protected $video;

    /**
     * Create a new notification instance.
     *
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', SparkChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        try {
            return (new MailMessage)
                ->line('Your video "' . $this->video->title . '" has been transcoded and is ready to be edited!')
                ->action('Edit Video', url('/projects/' . $this->video->project . '/edit/' . $this->video->id))
                ->line('Thank you for using MotionCTA!');
        } catch (\Exception $e) {}
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
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
            ->action('View Video', url('/projects/' . $this->video->project . '/edit/' . $this->video->id))
            ->icon('fa-video-camera')
            ->body('Your video has been transcoded and is ready to be edited!');
    }
}
