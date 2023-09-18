<?php

namespace App\Jobs\ShortUrl;

use Jenssegers\Agent\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\ShortUrl\ShortUrlRedirectionJob;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ShortUrlAfterResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $agent;


    /**
     * Create a new job instance.
     */
    public function __construct($data, Agent $agent)
    {
        $this->data = $data;
        $this->agent = $agent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $agent = $this->agent;
            $browser = $agent->browser();
            $browser_version = $agent->version($browser);
            $platform = $agent->platform();
            $operating_system_version = $agent->version($platform);
            $deviceType = "Unknown";

            if ($agent->isDesktop()) {
                $deviceType = "Desktop";
            }
            if ($agent->isMobile()) {
                $deviceType = "Mobile";
            }
            if ($agent->isTablet()) {
                $deviceType = "Tablet";
            }
            if ($agent->isRobot()) {
                $deviceType = "Robot";
            }

            $data = [
                'short_url_id' => $this->data['short_url_id'],
                'request_ip' => $this->data['request_ip'],
                'current_date' => $this->data['current_date'],
                'operating_system' => $platform,
                'operating_system_version' => $operating_system_version,
                'browser'                  => $browser,
                'browser_version'          => $browser_version,
                'device_type'              => $deviceType,
            ];

            ShortUrlRedirectionJob::dispatch($data);
        } catch (HttpException $th) {
            Log::channel('redirection')->error($th);
        }
    }
}
