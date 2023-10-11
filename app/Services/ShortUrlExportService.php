<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;
use App\Constants\ShortUrlConstant;


class ShortUrlExportService
{

    public function query(
        $data
    ) {

        $sortByKey = $data['sortByKey'];
        $sortByOrder = $data['sortByOrder'];
        $fromDateFilter = $data['fromDateFilter'];
        $toDateFilter = $data['toDateFilter'];
        $expireAtFilter = $data['expireAtFilter'];
        $statusFilter = $data['statusFilter'];
        $campaignId = $data['campaignId'];
        $shortUrl = $data['shortUrl'];
        $originalDomain = $data['originalDomain'];
        $tldFilter = $data['tldFilter'];
        $tld = $data['tld'];
        $isExportOriginalDomain = $data['isExportOriginalDomain'];

        return ShortUrl::query()
            ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                $query->withCount([
                    'visitorCount as visitor_count' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                            ->select(DB::raw('COALESCE(SUM(total_count), 0)'));
                    }
                ]);
            })
            ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                $query->withCount([
                    'visitorCount as visitor_count' => function ($query) {
                        $query->select(DB::raw('COALESCE(SUM(total_count), 0)'));
                    }
                ]);
            })
            ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                $query->with([
                    'campaign:id,name',
                    'type:id,name',
                    'visitorCountByCountries' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                            ->select([
                                'short_url_id',
                                'country',
                                DB::raw('SUM(total_count) as total_count')
                            ])
                            ->whereNotNull('country')
                            ->groupBy(['short_url_id', 'country'])
                            ->limit(5);
                    },
                    // 'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                    //     $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                    //         ->select([
                    //             'short_url_id',
                    //             'city',
                    //             DB::raw('SUM(total_count) as total_count')
                    //         ])
                    //         ->whereNotNull('city')
                    //         ->groupBy(['short_url_id', 'city'])
                    //         ->limit(5);
                    // },
                ]);
            })
            ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                $query->with([
                    'campaign:id,name',
                    'type:id,name',
                    'visitorCountByCountries' => function ($query) {
                        $query->select([
                            'short_url_id',
                            'country',
                            DB::raw('SUM(total_count) as total_count')
                        ])
                            ->whereNotNull('country')
                            ->groupBy(['short_url_id', 'country'])
                            ->limit(5);
                    },
                    // 'visitorCountByCities' => function ($query) {
                    //     $query->select([
                    //         'short_url_id',
                    //         'city',
                    //         DB::raw('SUM(total_count) as total_count')
                    //     ])
                    //         ->whereNotNull('city')
                    //         ->groupBy(['short_url_id', 'city'])
                    //         ->limit(5);
                    // },
                ]);
            })
            ->when($expireAtFilter && $expireAtFilter !== ShortUrlConstant::ALL, function ($query) use ($expireAtFilter) {
                $query->whereBetween('expired_at', [
                    now()->format('Y-m-d'),
                    now()->addDays((int) $expireAtFilter)->subDay()->format('Y-m-d')
                ]);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where(function ($subquery) use ($statusFilter) {
                    foreach ($statusFilter as $status) {
                        if ((int)$status === ShortUrlConstant::EXPIRED) {
                            $subquery->orWhere(function ($expiredSubquery) {
                                $expiredSubquery->where('status', ShortUrlConstant::EXPIRED)
                                    ->orWhere('expired_at', '<', now()->format('Y-m-d'));
                            });
                        } else {
                            $subquery->orWhere(function ($commonSubquery) use ($status) {
                                $commonSubquery->where('status', (int)$status)
                                    ->where('expired_at', '>', now()->format('Y-m-d'));
                            });
                        }
                    }
                });
            })
            ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                $query->where('campaign_id', $campaignId);
            })
            ->when($shortUrl, function ($query) use ($shortUrl) {
                $query->where('url_key', $shortUrl);
            })
            ->when($originalDomain, function ($query) use ($originalDomain) {
                $query->where('original_domain', 'LIKE', "%$originalDomain%");
            })
            ->when($tldFilter, function ($query) use ($tldFilter) {
                $query->where('tld_name', $tldFilter);
            })
            ->when(!$tldFilter && $tld, function ($query) use ($tld) {
                $query->where('tld_name', $tld);
            })
            ->orderBy($sortByKey, $sortByOrder)
            ->lazyById(1000, 'id')
            ->map(
                function ($shortUrl) use ($isExportOriginalDomain) {
                    return $this->map($shortUrl, $isExportOriginalDomain);
                }
            )
            ->all();
    }


    public function map($shortUrl, $isExportOriginalDomain): array
    {

        // Sort and format visitorCountByCountries
        $countryData = [];
        $visitorCountByCountries = $shortUrl->visitorCountByCountries->sortByDesc('total_count')->values()->all();
        foreach ($visitorCountByCountries as $country) {
            $countryData[] = "{$country->country}:{$country->total_count}";
        }

        // Sort and format visitorCountByCities
        // $cityData = [];
        // $visitorCountByCities = $shortUrl->visitorCountByCities->sortByDesc('total_count')->values()->all();
        // foreach ($visitorCountByCities as $city) {
        //     $cityData[] = "{$city->city}:{$city->total_count}";
        // }

        if (to_boolean($isExportOriginalDomain)) {
            $originalDomain = $shortUrl->original_domain ?? '-';
        } else {
            $originalDomain = '-';
        }

        return [
            'ID' => $shortUrl->url_key ?? '-',
            'Campaign Name' => @$shortUrl->campaign->name ?? '-',
            'Original Domain' => $originalDomain,
            'Destination Domain' => $shortUrl->destination_domain ?? '-',
            'Short URL' => $shortUrl->short_url ?? '-',
            'Total Visitor' => $shortUrl->visitor_count ?? '0',
            'TLD' => $shortUrl->tld_name  ?? '-',
            'TLD Price' => $shortUrl->tld_price ?? '-',
            'Type' => @$shortUrl->type->name ?? '-',
            'Auto Renewal' => $shortUrl->auto_renewal ? 'Yes' : 'No',
            'Status' => $this->getStatus((int) $shortUrl->status, $shortUrl->expired_at),
            'Expired On' => $shortUrl->expired_at ?? '-',
            '1st Country Visitor' => @$countryData[0] ? $countryData[0] . ':' . $this->getPercentageWithSign((int)$shortUrl->visitor_count, $this->getCountryCount($countryData[0])) : '-',
            '2nd Country Visitor' => @$countryData[1] ? $countryData[1] . ':' . $this->getPercentageWithSign((int)$shortUrl->visitor_count, $this->getCountryCount($countryData[1])) : '-',
            '3rd Country Visitor' => @$countryData[2] ? $countryData[2] . ':' . $this->getPercentageWithSign((int)$shortUrl->visitor_count, $this->getCountryCount($countryData[2])) : '-',
            '4th Country Visitor' => @$countryData[3] ? $countryData[3] . ':' . $this->getPercentageWithSign((int)$shortUrl->visitor_count, $this->getCountryCount($countryData[3])) : '-',
            '5th Country Visitor' => @$countryData[4] ? $countryData[4] . ':' . $this->getPercentageWithSign((int)$shortUrl->visitor_count, $this->getCountryCount($countryData[4])) : '-',
            // '1st City Visitor' => $cityData[0] ?? '-',
            // '2nd City Visitor' => $cityData[1] ?? '-',
            // '3rd City Visitor' => $cityData[2] ?? '-',
            // '4th City Visitor' => $cityData[3] ?? '-',
            // '5th City Visitor' => $cityData[4] ?? '-',
        ];
    }

    public function getStatus(int $status, $expiredAt): string
    {
        $currentDate = now()->format('Y-m-d');
        $expiredDate = Carbon::make($expiredAt)->format('Y-m-d');

        if ($expiredDate < $currentDate || $status === ShortUrlConstant::EXPIRED) {
            return 'Expired';
        } elseif ($expiredDate > $currentDate && $status === ShortUrlConstant::VALID) {
            return 'Valid';
        } else {
            return 'Invalid';
        }
    }

    public function itemsGenerator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }

    public function getPercentageWithSign($total, $value): string
    {
        if ($total === 0) return "0 %";
        $percentage = floor(($value / $total) * 100);
        return $percentage . " %";
    }

    // explode by : and get the second index
    public function getCountryCount($country): int
    {
        $country = explode(':', $country);

        return (int)$country[1];
    }
}
