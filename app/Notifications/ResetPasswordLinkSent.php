<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class ResetPasswordLinkSent extends ResetPassword
{
	use Queueable;
	
	/**
	 * Create a new notification instance.
	 *
	 * @param $token
	 * @return void
	 */
	public function __construct($token)
	{
		parent::__construct($token);
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
				->subject('Reset Password Notification')
				->greeting('Hello!')
				->line('You are receiving this email because we received a password reset request for your account.')
				->action('Reset Password', $link)
				->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
				->line('If you did not request a password reset, no further action is required.')
				->line('Thank you for using ' . config('app.name') . '!');
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
			->action('Reset Password', url('/reset-password/' . $this->token))
			->icon('fa-key')
			->body('Sent email for your password reset request!');
	}
}
