<?php

namespace App\Jobs\ShortUrl;

use App\Models\User;
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

    protected $user;
    protected $exportFileName;
    protected $exportFileDownloadLink;


    public function __construct(User $user, $exportFileName, $exportFileDownloadLink)
    {
        $this->user = $user;
        $this->exportFileName = $exportFileName;
        $this->exportFileDownloadLink = $exportFileDownloadLink;
    }

    public function handle()
    {
        $this->user->notify(new LatestDomainExportSuccessNotification($this->exportFileName, $this->exportFileDownloadLink));
    }
}
