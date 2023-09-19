<?php

namespace App\Notifications\ExcludedDomain;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ExcludedDomainExportSuccessNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $exportFileDownloadLink = URL::to($this->data['exportFileDownloadLink']);

        return (new MailMessage)
            ->success()
            ->subject('Exclude Domains Export Successfully Completed')
            ->line('Exclude Domains Export Successfully Completed For: ' . $this->data['exportFileName'])
            ->action('Download', $exportFileDownloadLink)
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
            'name' => $this->data['exportFileName'],
            'link' => $this->data['exportFileDownloadLink'],
        ];
    }
}
