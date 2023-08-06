<?php

namespace App\Imports\ShortUrl;

use App\Models\User;
use App\Models\Campaign;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Constants\ShortUrlConstant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use App\Notifications\ShortUrl\ShortUrlImportSuccessNotification;
use App\Notifications\ShortUrl\ShortUrlImportHasFailedNotification;

class ShortUrlImport implements ToModel,  WithChunkReading, ShouldQueue, WithEvents, WithBatchInserts, WithUpserts, WithUpsertColumns
{
    use Importable;
    use RegistersEventListeners;
    protected $importedBy;
    protected $campaign;
    protected $generateCodeAction;

    public function __construct(User $importedBy, Campaign $campaign)
    {
        $this->importedBy = $importedBy;
        $this->campaign = $campaign;
        $this->generateCodeAction =  new GenerateCodeAction();
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $this->importedBy->notify(new ShortUrlImportHasFailedNotification($this->campaign->name));
            },
            AfterImport::class => function (AfterImport $event) {
                $this->importedBy->notify(new ShortUrlImportSuccessNotification($this->campaign->name));
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
        if (!isset($row[0]) && !isset($row[1]) && !isset($row[2]) && !isset($row[3])) {
            return null;
        }

        $campaign_id = $this->campaign->id;
        $app_url = config('app.url');
        $code = $this->generateCodeAction->execute();
        $generatedUrl = $app_url . '/vx/';
        $short_url = $generatedUrl . $code;
        $originalUrl = removeHttpOrHttps($row[0]);
        $extractTld = extractTldFromDomain($originalUrl);

        $excludedDomainsExists = DB::table('excluded_domains')->where([
            'campaign_id' => $campaign_id,
            'domain' => $originalUrl,
        ])->exists();

        if (!$excludedDomainsExists) {

            $tld = DB::table('tlds')->select(['id'])->where([
                'campaign_id' => $campaign_id,
                'name' => $extractTld,
            ])->first();


            return new ShortUrl([
                'campaign_id' => $this->campaign->id,
                'original_domain' => $row[0],
                'destination_domain' => $row[1],
                'expired_at' => $row[2],
                'auto_renewal' => $row[3],
                'status' => ShortUrlConstant::INVALID,
                'short_url' => $short_url,
                'url_key' => $code,
                'tld_id' => @$tld->id ?? null,
                'domain_tld' => $extractTld,
                'tld_price' => @$tld->price ?? null,
                'created_by' => $this->importedBy->id,
                'updated_by' => $this->importedBy->id,
            ]);
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function upsertColumns()
    {
        return ['expired_at', 'auto_renewal', 'updated_by'];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'original_domain';
    }


    public function chunkSize(): int
    {
        return 1000;
    }
}
