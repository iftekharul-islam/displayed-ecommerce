<?php

namespace App\Jobs\ShortUrl;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Actions\CountryCityVisitorAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $this->onQueue('analytics');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $date = Carbon::make('2023-01-01')->format('Y-m-d');

            DB::connection('choto_analytics_db')
                ->table('analytics')
                ->where('is_count', false)
                ->whereDate('created_at', '>=', $date)
                ->lazyById(1000, 'id')
                ->each(function ($analytic) {
                    $date = Carbon::make($analytic->created_at)->format('Y-m-d');

                    $data = [
                        'short_url_id' => $analytic->shortenerurl_id,
                        'request_ip' => $analytic->ip_address,
                        'date' => $date,
                    ];

                    $this->countryCityVisitorAction->execute($data);

                    DB::connection('choto_analytics_db')
                        ->table('analytics')
                        ->where('id', $analytic->id)
                        ->update(['is_count' => true]);
                });
        } catch (HttpException $th) {
            Log::channel('redirection')->error($th->getMessage());
        }
    }
}
