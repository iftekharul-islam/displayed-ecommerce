<?php

namespace App\Exports\ShortUrl;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Notifications\ShortUrl\ShortUrlExportFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class ShortUrlExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    use Exportable;
    use Queueable;

    protected $exportedBy;
    protected $exportFileName;
    protected $campaignId;
    protected $fromDateFilter;
    protected $toDateFilter;
    protected $expireAtFilter;
    protected $statusFilter;
    protected $tldFilter;
    protected $originalDomain;
    protected $shortUrl;
    protected $tld;
    protected $isExportOriginalDomain;


    public function __construct(User $exportedBy, $data, bool $isExportOriginalDomain)
    {
        $this->exportedBy =  $exportedBy;
        $this->exportFileName = $data['exportFileName'];
        $this->campaignId = $data['campaignId'];
        $this->fromDateFilter = $data['fromDateFilter'];
        $this->toDateFilter = $data['toDateFilter'];
        $this->expireAtFilter = $data['expireAtFilter'];
        $this->statusFilter = $data['statusFilter'];
        $this->tldFilter = $data['tldFilter'];
        $this->originalDomain = $data['originalDomain'];
        $this->shortUrl = $data['shortUrl'];
        $this->tld = $data['tld'];
        $this->isExportOriginalDomain = $isExportOriginalDomain;
    }

    public function failed(Throwable $exception): void
    {
        $this->exportedBy->notify(new ShortUrlExportFailedNotification($this->exportFileName));
        Log::error($exception);
    }

    public function query()
    {
        $campaignId = $this->campaignId;
        $fromDateFilter = $this->fromDateFilter;
        $toDateFilter = $this->toDateFilter;
        $expireAtFilter = $this->expireAtFilter;
        $statusFilter = $this->statusFilter;
        $tldFilter = $this->tldFilter;
        $originalDomain = $this->originalDomain;
        $shortUrl = $this->shortUrl;
        $tld = $this->tld;

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
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Campaign Name',
            'Original Domain',
            'Destination Domain',
            'Short URL',
            'Total Visitor',
            'TLD',
            'TLD Price',
            'Auto Renewal',
            'Status',
            'Expired At',
            '1st Country Visitor',
            '2nd Country Visitor',
            '3rd Country Visitor',
            '4th Country Visitor',
            '5th Country Visitor',
            '1st City Visitor',
            '2nd City Visitor',
            '3rd City Visitor',
            '4th City Visitor',
            '5th City Visitor',
        ];
    }

    public function map($shortUrl): array
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

        if ($this->isExportOriginalDomain) {
            $originalDomain = $shortUrl->original_domain  ?? '-';
        } else {
            $originalDomain = '-';
        }

        return [
            $shortUrl->campaign->name ?? '-',
            $originalDomain,
            $shortUrl->destination_domain ?? '-',
            $shortUrl->short_url ?? '-',
            $shortUrl->visitor_count ?? '0',
            $shortUrl->tld_name  ?? '-',
            $shortUrl->tld_price ?? '-',
            $shortUrl->auto_renewal ? 'Yes' : 'No',
            $this->getStatus((int) $shortUrl->status, $shortUrl->expired_at),
            $shortUrl->expired_at ?? '-',
            $country1st,
            $country2nd,
            $country3rd,
            $country4th,
            $country5th,
            $city1st,
            $city2nd,
            $city3rd,
            $city4th,
            $city5th,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 40,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'k' => 30,
            'L' => 30,
            'M' => 30,
            'N' => 30,
            'O' => 30,
            'P' => 30,
            'Q' => 30,
            'R' => 30,
            'S' => 30,
            'T' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()
                    ->getStyle('A:T')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
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
}
