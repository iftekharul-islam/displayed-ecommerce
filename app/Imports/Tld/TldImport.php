<?php

namespace App\Imports\Tld;

use App\Models\Campaign;
use App\Models\Tld;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Notifications\Tld\TldImportSuccessNotification;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use App\Notifications\Tld\TldImportHasFailedNotification;

class TldImport implements ToModel, ShouldQueue, WithChunkReading, WithEvents
{
    use Importable;
    use RegistersEventListeners;
    protected $importedBy;
    protected $campaign;

    public function __construct(User $importedBy, Campaign $campaign)
    {
        $this->importedBy = $importedBy;
        $this->campaign = $campaign;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $this->importedBy->notify(new TldImportHasFailedNotification($this->campaign->name));
            },
            AfterImport::class => function (AfterImport $event) {
                $this->importedBy->notify(new TldImportSuccessNotification($this->campaign->name));
            },
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!isset($row[0]) && !isset($row[1])) {
            return null;
        }

        $price = getPriceWithoutDollarSign($row[1]);

        return  Tld::updateOrCreate(
            [
                'campaign_id' => $this->campaign->id,
                'name' => $row[0],
            ],
            [
                'campaign_id' => $this->campaign->id,
                'name' => $row[0],
                'price' => '$' . $price,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
