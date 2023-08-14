<?php

namespace App\Notifications\ShortUrl;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShortUrlExportSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;
    protected $exportFileDownloadLink;

    /**
     * Create a new notification instance.
     */
    public function __construct($name, $exportFileDownloadLink)
    {
        $this->name = $name;
        $this->exportFileDownloadLink = $exportFileDownloadLink;
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
            ->subject('Short Url Export Successfully Completed')
            ->line('Short Url Export Successfully Completed Name: ' . $this->name)
            ->action('Download', $this->exportFileDownloadLink)
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
