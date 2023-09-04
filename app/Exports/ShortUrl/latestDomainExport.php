<?php

namespace App\Exports\ShortUrl;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Notifications\ShortUrl\LatestDomainExportFailedNotification;

class latestDomainExport implements
    FromQuery,
    ShouldQueue,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    use Exportable;

    protected $exportedBy;
    protected $exportFileName;
    protected $campaignId;
    protected $fromDate;
    protected $toDate;

    public function __construct(User $exportedBy, $data)
    {
        $this->exportedBy =  $exportedBy;
        $this->exportFileName = $data['exportFileName'];
        $this->campaignId = $data['campaignId'];
        $this->fromDate = $data['fromDate'];
        $this->toDate = $data['toDate'];
    }

    public function failed(Throwable $exception): void
    {
        $this->exportedBy->notify(new LatestDomainExportFailedNotification($this->exportFileName));
        Log::error($exception);
    }

    public function query()
    {
        $campaignId = $this->campaignId;
        $fromDate =  Carbon::make($this->fromDate)->format('Y-m-d 00:00:00');
        $toDate =  Carbon::make($this->toDate)->format('Y-m-d 23:59:59');

        return ShortUrl::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('campaign_id', $campaignId)
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Original Domain',
            'Short URL',
        ];
    }

    public function map($shortUrl): array
    {
        return [
            $shortUrl->original_domain ?? '-',
            $shortUrl->short_url ?? '-',
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
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()
                    ->getStyle('A:B')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
