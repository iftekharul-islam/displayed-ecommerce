<?php

namespace App\Notifications\ShortUrl;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TldUpdateSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;

    /**
     * Create a new notification instance.
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->onConnection('database');
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->subject('Short Urls Tld Update Successfully Completed For: ' . $this->name)
            ->line('Short Urls Tld Update Successfully Completed Name: ' . $this->name)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
