<?php

namespace App\Notifications\Notice;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackNoticeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $notice;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notice)
    {
        $this->notice = $notice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(object $notifiable)
    {
        return (new SlackMessage)
            ->content('*'. 'New Notice Published' . ' : ' . ucfirst($this->notice->title) . '*'."\n" . strip_tags($this->notice->description));
    }

}
