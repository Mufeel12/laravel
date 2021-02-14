<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class SocialRegisterMail extends Notification
{
    use Queueable;

    protected $type;
    protected $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( $user,$type )
    {
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', SparkChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws
     */
    public function toMail($notifiable)
    {
        
        return (new MailMessage)
                    ->subject('Thank you signing up on Adilo!')
                    ->greeting('Hello ' . $notifiable->full_name)
                    ->line('Thanks for signing up to Adilo free forever plan.')
                    ->line('Sign up mode: '.$this->type. ' login')
                    ->line('Plan: Free Forever (upgrade anytime you want)')
                    ->line('You can now host and stream your videos for free, comment on videos you love and so much more.')
                    ->line('If you want to learn more about Adilo business video hosting, visit our official website.')
                    ->line('Login to your Adilo: ' . config('env.ROOT_URL'))
                    ->line('Thank you for using ' . config('app.name') . '!')
                    ->from('accounts@bigcommand.com', 'Bigcommand Accounts');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
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
            ->action('Resend',url('/confirm-email/'.$this->user->email_verified_token))
            ->body('The verification email has been resent successfully.');
    }
}