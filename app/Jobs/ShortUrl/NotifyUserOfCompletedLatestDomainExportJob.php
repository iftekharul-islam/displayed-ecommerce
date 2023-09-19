<?php

namespace App\Jobs\ShortUrl;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Notifications\ShortUrl\LatestDomainExportSuccessNotification;

class NotifyUserOfCompletedLatestDomainExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exportedBy;
    protected $data;


    public function __construct($data)
    {
        $this->exportedBy = $data['exportedBy'];
        $this->data = $data;
    }

    public function handle()
    {
        $this->exportedBy->notify(new LatestDomainExportSuccessNotification($this->data));
    }
}
