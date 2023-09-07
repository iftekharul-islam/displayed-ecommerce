<?php

namespace App\Actions;

use Carbon\Carbon;
use App\Actions\ProIpApi;
use App\Models\VisitorCountByCity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VisitorCountByCountry;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CountryCityVisitorAction
{
    public function execute($data)
    {
        try {
            $short_url_id = $data['short_url_id'];
            $request_ip = $data['request_ip'];
            $date = Carbon::make($data['date'])->format('Y-m-d');

            DB::transaction(function () use ($short_url_id, $request_ip, $date) {

                // location get

                if ($position = ProIpApi::location($request_ip)) {
                    $countryName = @$position['country'];
                    $cityName = @$position['city'];


                    // Total visitor count by country
                    if (!empty($countryName)) {
                        $visitorCountByCountryExist = VisitorCountByCountry::firstOrNew([
                            'short_url_id' => $short_url_id,
                            'country' => $countryName,
                            'visited_at' => $date,
                        ]);

                        if (!$visitorCountByCountryExist->exists) {
                            $visitorCountByCountryExist->fill([
                                'short_url_id' => $short_url_id,
                                'country' => $countryName,
                                'visited_at' => $date,
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
                            'visited_at' => $date,
                        ]);

                        if (!$visitorCountByCityExist->exists) {
                            $visitorCountByCityExist->fill([
                                'short_url_id' => $short_url_id,
                                'city' => $cityName,
                                'visited_at' => $date,
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
