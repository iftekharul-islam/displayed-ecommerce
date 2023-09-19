<?php

namespace App\Jobs\ExcludedDomain;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use OpenSpout\Common\Entity\Style\Style;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\ExcludedDomain\ExcludedDomainExportService;
use App\Notifications\ExcludedDomain\ExcludedDomainExportFailedNotification;
use App\Notifications\ExcludedDomain\ExcludedDomainExportSuccessNotification;

class ExcludedDomainExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $excludedDomainExportService;
    protected $exportedBy;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->excludedDomainExportService = new ExcludedDomainExportService();
        $this->exportedBy = $this->data['exportedBy'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $exportFilePath = storage_path("app/public/{$this->data['exportFilePath']}");
            Storage::disk('public')->put("{$this->data['exportFilePath']}", '');

            $data = $this->excludedDomainExportService->query(
                $this->data
            );

            $header_style = (new Style())
                ->setFontBold()
                ->setShouldWrapText()
                ->setCellAlignment('center');


            $rows_style = (new Style())
                ->setShouldWrapText()
                ->setCellAlignment('center');

            (new FastExcel($this->excludedDomainExportService->itemsGenerator($data)))
                ->headerStyle($header_style)
                ->rowsStyle($rows_style)
                ->export($exportFilePath);

            $this->exportedBy->notify(new ExcludedDomainExportSuccessNotification($this->data));
        } catch (HttpException $th) {
            Log::error($th->getMessage());
        }
    }


    /**
     * The job failed to process.
     *
     * @param  \Throwable  $th
     * @return void
     */
    public function failed(\Throwable $th)
    {
        $this->exportedBy->notify(new ExcludedDomainExportFailedNotification($this->data));
        Log::error($th->getMessage());
    }
}
