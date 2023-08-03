<?php

namespace App\Imports\Tld;

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
    public $importedBy;
    public $campaign_id;

    public function __construct(User $importedBy, $campaign_id)
    {
        $this->importedBy = $importedBy;
        $this->campaign_id = $campaign_id;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $this->importedBy->notify(new TldImportHasFailedNotification);
            },
            AfterImport::class => function (AfterImport $event) {
                $this->importedBy->notify(new TldImportSuccessNotification);
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
        if (!isset($row[0])) {
            return null;
        }

        return  Tld::updateOrCreate(
            [
                'campaign_id' => $this->campaign_id,
                'name' => $row[0],
            ],
            [
                'campaign_id' => $this->campaign_id,
                'name' => $row[0],
                'price' => $row[1],
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
