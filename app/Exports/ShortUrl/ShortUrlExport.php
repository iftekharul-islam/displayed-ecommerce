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

class ShortUrlExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    use Exportable;
    protected $exportedBy;
    protected $exportFileName;
    protected $campaignId;
    protected $fromDateFilter;
    protected $toDateFilter;
    protected $expireAtFilter;
    protected $statusFilter;
    protected $tldFilter;

    public function __construct(User $exportedBy, $data)
    {
        $this->exportedBy =  $exportedBy;
        $this->exportFileName = $data['exportFileName'];
        $this->campaignId = $data['campaignId'];
        $this->fromDateFilter = $data['fromDateFilter'];
        $this->toDateFilter = $data['toDateFilter'];
        $this->expireAtFilter = $data['expireAtFilter'];
        $this->statusFilter = $data['statusFilter'];
        $this->tldFilter = $data['tldFilter'];
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
                        ])->groupBy(['short_url_id', 'country'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
                    'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                            'short_url_id',
                            'city',
                            DB::raw('SUM(total_count) as total_count'),
                        ])->groupBy(['short_url_id', 'city'])
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
                        ])->groupBy(['short_url_id', 'country'])
                            ->orderBy('total_count', 'desc')
                            ->limit(5);
                    },
                    'visitorCountByCities' => function ($query) {
                        $query->select([
                            'short_url_id',
                            'city',
                            DB::raw('SUM(total_count) as total_count'),
                        ])->groupBy(['short_url_id', 'city'])
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
            ->when($tldFilter, function ($query) use ($tldFilter) {
                $query->where('su_tld_name', 'ILIKE', "%$tldFilter%");
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
        ];
    }

    public function map($shortUrl): array
    {
        return [
            $shortUrl->campaign->name ?? '-',
            $shortUrl->original_domain ?? '-',
            $shortUrl->destination_domain ?? '-',
            $shortUrl->short_url ?? '-',
            $shortUrl->visitor_count ?? 0,
            $shortUrl->su_tld_name  ?? '-',
            $shortUrl->su_tld_price ?? '-',
            $shortUrl->auto_renewal ? 'Yes' : 'No',
            $this->getStatus((int) $shortUrl->status, $shortUrl->expired_at),
            $shortUrl->expired_at ?? '-',
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
                    ->getStyle('A:J')
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
