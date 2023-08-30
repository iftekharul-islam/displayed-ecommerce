<?php

namespace App\Jobs\ShortUrl;

use App\Models\Analytics;
use Jenssegers\Agent\Agent;
use App\Models\VisitorCount;
use Illuminate\Bus\Queueable;
use App\Models\VisitorCountByCity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VisitorCountByCountry;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Stevebauman\Location\Facades\Location;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ShortUrlRedirectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $short_url_id;
    protected $request_ip;

    /**
     * Create a new job instance.
     */
    public function __construct($short_url_id, $request_ip)
    {
        $this->short_url_id = $short_url_id;
        $this->request_ip = $request_ip;
        $this->onQueue('redirection');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $short_url_id = $this->short_url_id;
            $request_ip = $this->request_ip;
            $currenDate = now()->format('Y-m-d');

            DB::transaction(function () use ($short_url_id, $request_ip, $currenDate) {

                // Store Data into analytics table
                $agent = new Agent();
                $browser = $agent->browser();
                $platform = $agent->platform();
                $deviceType = "Unknown";

                $operating_system_version = $agent->version($platform);
                $browser_version = $agent->version($browser);

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

                Analytics::create([
                    'short_url_id'             => $short_url_id,
                    'operating_system'         => $platform,
                    'operating_system_version' => $operating_system_version,
                    'browser'                  => $browser,
                    'browser_version'          => $browser_version,
                    'device_type'              => $deviceType,
                    'ip_address'               => $request_ip,
                ]);


                // Total visitor count
                $visitorCountExist = VisitorCount::firstOrNew([
                    'short_url_id' => $short_url_id,
                    'visited_at' => $currenDate,
                ]);

                if (!$visitorCountExist->exists) {
                    $visitorCountExist->fill([
                        'short_url_id' => $short_url_id,
                        'visited_at' => $currenDate,
                        'total_count' => 1,
                    ]);
                } else {
                    $visitorCountExist->total_count += 1;
                }

                $visitorCountExist->save();
            });


            DB::transaction(function () use ($short_url_id, $request_ip, $currenDate) {

                // location get
                $location  = Location::get($request_ip);
                $countryName = @$location->countryName;
                $cityName = @$location->cityName;

                // Total visitor count by country
                $visitorCountByCountryExist = VisitorCountByCountry::firstOrNew([
                    'short_url_id' => $short_url_id,
                    'country' => $countryName,
                    'visited_at' => $currenDate,
                ]);

                if (!$visitorCountByCountryExist->exists) {
                    $visitorCountByCountryExist->fill([
                        'short_url_id' => $short_url_id,
                        'country' => $countryName,
                        'visited_at' => $currenDate,
                        'total_count' => 1,
                    ]);
                } else {
                    $visitorCountByCountryExist->total_count += 1;
                }

                $visitorCountByCountryExist->save();

                // Total visitor count by city
                $visitorCountByCityExist = VisitorCountByCity::firstOrNew([
                    'short_url_id' => $short_url_id,
                    'city' => $cityName,
                    'visited_at' => $currenDate,
                ]);

                if (!$visitorCountByCityExist->exists) {
                    $visitorCountByCityExist->fill([
                        'short_url_id' => $short_url_id,
                        'city' => $cityName,
                        'visited_at' => $currenDate,
                        'total_count' => 1,
                    ]);
                } else {
                    $visitorCountByCityExist->total_count += 1;
                }

                $visitorCountByCityExist->save();
            });
        } catch (HttpException $th) {
            Log::channel('redirection')->error($th);
        }
    }
}
