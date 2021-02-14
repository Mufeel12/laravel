<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class VerifyEmail extends Notification
{
    use Queueable;

    protected $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( $user )
    {
        $this->user = $user;
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
        try {
            $link = url('/confirm-email/'.$this->user->email_verified_token);
            return (new MailMessage)
                ->subject('Confirm Your Adilo Account - Verify Your Email.')
                ->from(config('app.mail_from.address'), config('app.mail_from.name'))
                ->greeting('Hello! ' . $this->user->name )
                ->line('Thank you for signing up to Adilo.')
                ->line('You\'re just one step away from completing your sign up.')
                ->action('Verify Your Account', $link)
                ->line('Your new Adilo account after verification will work for all Bigcommand application.')
                ->line('If you have any questions or need help, get in touch with our support team.https://help.bigcommand.com');
        } catch (\Exception $e) {
        }
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