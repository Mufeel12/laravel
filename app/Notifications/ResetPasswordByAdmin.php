<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class ResetPasswordByAdmin extends ResetPassword
{
    use Queueable;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param $token
     * @return void
     */
    public function __construct($token, $user)
    {
        parent::__construct($token);
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
            $link = url("/reset-password/" . $this->token);

            return (new MailMessage)
                ->subject('[Critical] A prompt to reset your password')
                ->from('support@bigcommand.com', 'BigCommand Support')
                ->greeting('Hello ' . $this->user->full_name)
                ->line('A member of the of BigCommand support team managing your Adilo account has prompted that you reset your password.')
                ->line('Click the link below.')
                ->action('Reset Password', $link)
                ->line("When choosing a new password, make sure it's secure containing at least:")
                ->line("- 8 characters")
                ->line("- capital letter")
                ->line("- small case letter")
                ->line("- number")
                ->line("- special character")
                ->line("If you're unsure about this change contact the BigCommand support team at https://help.bigcommand.com")
                ->line("Regards,")
                ->line("Bigcommand LLC")
                ->line("108 West 13th Street,")
                ->line("Wilmington, DE")
                ->line("19801");
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
        $link = url("/reset-password/" . $this->token);

        return (new SparkNotification)
            ->action('View Task', $link)
            ->icon('fa-users')
            ->body('A team member completed a task!');
    }
}
