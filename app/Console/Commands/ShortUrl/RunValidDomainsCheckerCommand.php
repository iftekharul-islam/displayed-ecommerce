<?php

namespace App\Console\Commands\ShortUrl;

use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\ShortUrl\ValidDomainCheckJob;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RunValidDomainsCheckerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-valid-domains-checker-command';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All domains checker';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $campaigns = Campaign::query()
                ->select(['id', 'name'])
                ->where('is_active', true)
                ->get();

            $mailsTo = [
                config('app.valid_domain_checker_email')
            ];

            foreach ($campaigns as $campaign) {
                ValidDomainCheckJob::dispatch($mailsTo, $campaign);
            }
        } catch (HttpException $th) {
            Log::error($th);
        }
    }
}
