<?php

namespace App\Jobs\ShortUrl;

use Carbon\Carbon;

use App\Models\Analytics;
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

    protected $data;


    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;

        $this->onQueue('redirection');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $short_url_id = $this->data['short_url_id'];
            $request_ip = $this->data['request_ip'];
            $current_date = Carbon::make($this->data['current_date'])->format('Y-m-d');

            DB::transaction(function () use ($short_url_id, $request_ip, $current_date) {
                // Store Data into analytics table
                Analytics::create([
                    'short_url_id'             => $short_url_id,
                    'operating_system'         => $this->data['operating_system'],
                    'operating_system_version' => $this->data['operating_system_version'],
                    'browser'                  => $this->data['browser'],
                    'browser_version'          => $this->data['browser_version'],
                    'device_type'              => $this->data['device_type'],
                    'ip_address'               => $request_ip,
                ]);


                // Total visitor count
                $visitorCountExist = VisitorCount::firstOrNew([
                    'short_url_id' => $short_url_id,
                    'visited_at' => $current_date,
                ]);

                if (!$visitorCountExist->exists) {
                    $visitorCountExist->fill([
                        'short_url_id' => $short_url_id,
                        'visited_at' => $current_date,
                        'total_count' => 1,
                    ]);
                } else {
                    $visitorCountExist->total_count += 1;
                }

                $visitorCountExist->save();
            });


            DB::transaction(function () use ($short_url_id, $request_ip, $current_date) {

                // location get

                if ($position = Location::get($request_ip)) {
                    $countryName = @$position->countryName;
                    $cityName = @$position->cityName;


                    // Total visitor count by country
                    if (!empty($countryName)) {
                        $visitorCountByCountryExist = VisitorCountByCountry::firstOrNew([
                            'short_url_id' => $short_url_id,
                            'country' => $countryName,
                            'visited_at' => $current_date,
                        ]);

                        if (!$visitorCountByCountryExist->exists) {
                            $visitorCountByCountryExist->fill([
                                'short_url_id' => $short_url_id,
                                'country' => $countryName,
                                'visited_at' => $current_date,
                                'total_count' => 1,
                            ]);
                        } else {
                            $visitorCountByCountryExist->total_count += 1;
                        }

                        $visitorCountByCountryExist->save();
                    }

                    // Total visitor count by city
                    if (!empty($cityName)) {

                        $visitorCountByCityExist = VisitorCountByCity::firstOrNew([
                            'short_url_id' => $short_url_id,
                            'city' => $cityName,
                            'visited_at' => $current_date,
                        ]);

                        if (!$visitorCountByCityExist->exists) {
                            $visitorCountByCityExist->fill([
                                'short_url_id' => $short_url_id,
                                'city' => $cityName,
                                'visited_at' => $current_date,
                                'total_count' => 1,
                            ]);
                        } else {
                            $visitorCountByCityExist->total_count += 1;
                        }

                        $visitorCountByCityExist->save();
                    }
                }
            });
        } catch (HttpException $th) {
            Log::channel('redirection')->error($th->getMessage());
        }
    }
}
