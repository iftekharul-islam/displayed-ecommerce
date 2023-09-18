<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ShortUrl\CountryCityVisitorJob;

class CountryCityVisitorCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:country-city-visitor-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'country and city visitor count from analytics table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CountryCityVisitorJob::dispatch();
    }
}
