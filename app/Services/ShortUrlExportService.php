<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;
use App\Constants\ShortUrlConstant;


class ShortUrlExportService
{

    public function query(
        $fromDateFilter,
        $toDateFilter,
        $expireAtFilter,
        $statusFilter,
        $campaignId,
        $shortUrl,
        $originalDomain,
        $tldFilter,
        $tld
    ) {

        return ShortUrl::query()
            ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                $query->withCount([
                    'visitorCount as visitor_count' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select(DB::raw('SUM(total_count)'));
                    }
                ]);
            })
            ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                $query->withCount([
                    'visitorCount as visitor_count' => function ($query) {
                        $query->select(DB::raw('SUM(total_count)'));
                    }
                ]);
            })
            ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                $query->with([
                    'campaign',
                    'visitorCountByCountries' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                            'short_url_id',
                            'country',
                            DB::raw('SUM(total_count) as total_count'),
                        ])
                            ->whereNotNull('country')
                            ->groupBy(['short_url_id', 'country'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
                    'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                            'short_url_id',
                            'city',
                            DB::raw('SUM(total_count) as total_count'),
                        ])
                            ->whereNotNull('city')
                            ->groupBy(['short_url_id', 'city'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
                ]);
            })
            ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                $query->with([
                    'campaign',
                    'visitorCountByCountries' => function ($query) {
                        $query->select([
                            'short_url_id',
                            'country',
                            DB::raw('SUM(total_count) as total_count'),
                        ])
                            ->whereNotNull('country')
                            ->groupBy(['short_url_id', 'country'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
                    'visitorCountByCities' => function ($query) {
                        $query->select([
                            'short_url_id',
                            'city',
                            DB::raw('SUM(total_count) as total_count'),
                        ])
                            ->whereNotNull('city')
                            ->groupBy(['short_url_id', 'city'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
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
                $query->where('original_domain', $originalDomain);
            })
            ->when($tldFilter, function ($query) use ($tldFilter) {
                $query->where('tld_name', 'LIKE', "%$tldFilter%");
            })
            ->when(!$tldFilter && $tld, function ($query) use ($tld) {
                $query->where('tld_name', 'LIKE', "%$tld%");
            })
            ->get();
    }


    public function map($shortUrl, $isExportOriginalDomain): array
    {
        $countryVisitor1st = $shortUrl->visitorCountByCountries[0]->country ?? '-';
        $countryVisitorTotalCount1st = $shortUrl->visitorCountByCountries[0]->total_count ?? '-';
        $country1st = "$countryVisitor1st:$countryVisitorTotalCount1st";

        $countryVisitor2nd = $shortUrl->visitorCountByCountries[1]->country ?? '-';
        $countryVisitorTotalCount2nd = $shortUrl->visitorCountByCountries[1]->total_count ?? '-';
        $country2nd = "$countryVisitor2nd:$countryVisitorTotalCount2nd";

        $countryVisitor3rd = $shortUrl->visitorCountByCountries[2]->country ?? '-';
        $countryVisitorTotalCount3rd = $shortUrl->visitorCountByCountries[2]->total_count ?? '-';
        $country3rd = "$countryVisitor3rd:$countryVisitorTotalCount3rd";

        $countryVisitor4th = $shortUrl->visitorCountByCountries[3]->country ?? '-';
        $countryVisitorTotalCount4th = $shortUrl->visitorCountByCountries[3]->total_count ?? '-';
        $country4th = "$countryVisitor4th:$countryVisitorTotalCount4th";

        $countryVisitor5th = $shortUrl->visitorCountByCountries[4]->country ?? '-';
        $countryVisitorTotalCount5th = $shortUrl->visitorCountByCountries[4]->total_count ?? '-';
        $country5th = "$countryVisitor5th:$countryVisitorTotalCount5th";

        $cityVisitor1st = $shortUrl->visitorCountByCities[0]->city ?? '-';
        $cityVisitorTotalCount1st = $shortUrl->visitorCountByCities[0]->total_count ?? '-';
        $city1st = "$cityVisitor1st:$cityVisitorTotalCount1st";

        $cityVisitor2nd = $shortUrl->visitorCountByCities[1]->city ?? '-';
        $cityVisitorTotalCount2nd = $shortUrl->visitorCountByCities[1]->total_count ?? '-';
        $city2nd = "$cityVisitor2nd:$cityVisitorTotalCount2nd";

        $cityVisitor3rd = $shortUrl->visitorCountByCities[2]->city ?? '-';
        $cityVisitorTotalCount3rd = $shortUrl->visitorCountByCities[2]->total_count ?? '-';
        $city3rd = "$cityVisitor3rd:$cityVisitorTotalCount3rd";

        $cityVisitor4th = $shortUrl->visitorCountByCities[3]->city ?? '-';
        $cityVisitorTotalCount4th = $shortUrl->visitorCountByCities[3]->total_count ?? '-';
        $city4th = "$cityVisitor4th:$cityVisitorTotalCount4th";

        $cityVisitor5th = $shortUrl->visitorCountByCities[4]->city ?? '-';
        $cityVisitorTotalCount5th = $shortUrl->visitorCountByCities[4]->total_count ?? '-';
        $city5th = "$cityVisitor5th:$cityVisitorTotalCount5th";

        if ($isExportOriginalDomain) {
            $originalDomain = $shortUrl->original_domain  ?? '-';
        } else {
            $originalDomain = '-';
        }

        return [
            'Campaign Name' => $shortUrl->campaign->name ?? '-',
            'Original Domain' => $originalDomain,
            'Destination Domain' => $shortUrl->destination_domain ?? '-',
            'Short URL' => $shortUrl->short_url ?? '-',
            'Total Visitor' => $shortUrl->visitor_count ?? '0',
            'TLD' => $shortUrl->tld_name  ?? '-',
            'TLD Price' => $shortUrl->tld_price ?? '-',
            'Auto Renewal' => $shortUrl->auto_renewal ? 'Yes' : 'No',
            'Status' => $this->getStatus((int) $shortUrl->status, $shortUrl->expired_at),
            'Expired At' => $shortUrl->expired_at ?? '-',
            '1st Country Visitor' =>  $country1st,
            '2nd Country Visitor' => $country2nd,
            '3rd Country Visitor' => $country3rd,
            '4th Country Visitor' => $country4th,
            '5th Country Visitor' => $country5th,
            '1st City Visitor' => $city1st,
            '2nd City Visitor' => $city2nd,
            '3rd City Visitor' =>  $city3rd,
            '4th City Visitor' =>  $city4th,
            '5th City Visitor' =>  $city5th,
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
}
