<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class PasswordChanged extends Notification
{
	use Queueable;
	
	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
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
			return (new MailMessage)
				->subject('Password Changed')
				->greeting('Hello!')
				->line('Your password has been changed')
				->action(config('app.name'), url('/'))
				->line('Thank you for using our application!');
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
			->icon('fas fa-key')
			->body('Your authentication password has been changed.');
	}
}
