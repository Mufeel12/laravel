<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

class AccountDeleted extends Notification
{
    use Queueable;

    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
            return (new MailMessage)
                ->subject('Your Adilo account has been closed!')
                ->greeting('Hello ' . $notifiable->full_name)
                ->line('This to notify you that your Adilo account has been permanently closed.')
                ->line('Reason: ')
                ->line('If you think this was a mistake, please contact our support team right now for an appeal. [https://help.bigcommand.com]')
                ->line('NOTE: You have only 90 days from the date of receiving this email to regain access to your account and all your content. After 90 days, your data will be permanently wiped and can not be restored again.')
                ->line('Thank you for using ' . config('app.name') . '!')
                ->from('compliance@bigcommand.com', 'BigCommand Compliance');
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
}
