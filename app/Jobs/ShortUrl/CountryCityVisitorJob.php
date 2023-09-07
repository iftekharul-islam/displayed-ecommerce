<?php

namespace App\Jobs\ShortUrl;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Actions\CountryCityVisitorAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CountryCityVisitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $countryCityVisitorAction;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->countryCityVisitorAction = new CountryCityVisitorAction();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = [];
        $this->countryCityVisitorAction->execute($data);
    }
}
