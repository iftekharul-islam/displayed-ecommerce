<?php

namespace App\Jobs\ShortUrl;

use App\Models\User;
use App\Models\Campaign;
use App\Models\ShortUrl;
use Illuminate\Bus\Queueable;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Notifications\ShortUrl\ValidDomainCheckFailNotification;
use App\Notifications\ShortUrl\ValidDomainCheckSuccessNotification;

class ValidDomainCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    protected $createdBy;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $createdBy, Campaign $campaign)
    {
        $this->createdBy = $createdBy;
        $this->campaign = $campaign;

        // $this->onConnection('database');
        // $this->onQueue('valid-domain-check-cronjob');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $campaignId = $this->campaign->id;
        $campaignName = $this->campaign->name;
        $logPrefix = 'ValidDomainCheckJob: ' . $campaignName . ' - ';

        info($logPrefix . 'started');

        try {
            ShortUrl::query()
                ->select(['id', 'campaign_id', 'original_domain', 'expired_at'])
                ->where([
                    'campaign_id' => $campaignId,
                ])
                ->lazyById(1000, 'id')
                ->each(function (ShortUrl $shortUrl) use ($campaignName, $logPrefix) {
                    $now = now();
                    $message = 'Invalid';
                    $remarks = 'invalid and last checked on ' . $now->format('l') . ' - ' . $now->format('F d, Y');
                    $originalDomain = 'http://' . $shortUrl->original_domain;
                    $status = ShortUrlConstant::INVALID;

                    if ($shortUrl->expired_at < $now->format('Y-m-d')) {
                        $message = 'Expired';
                        $status = ShortUrlConstant::EXPIRED;
                        $remarks = "Expired{$shortUrl->id} - $originalDomain and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                    } else {

                        try {
                            $response = Http::withHeaders(['User-Agent' => 'Sajib/DJDJD/0.1'])->get($originalDomain);
                            $responseBody = $response->body();

                            if (preg_match('/<title>(.*?)<\/title>/', $responseBody, $matches)) {
                                $title = $matches[1];
                                $message = $status === 200 ? 'Valid' : 'Invalid';
                                $status = strpos($title, 'Lotto60') !== false ? ShortUrlConstant::VALID : ShortUrlConstant::INVALID;
                                $remarks = ($status === 200 ? 'Valid' : 'Invalid') . " and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                            }

                            $shortUrl->update([
                                'status' => $status,
                                'remarks' => $remarks,
                                'updated_at' => $now->format('Y-m-d H:i:s'),
                            ]);

                            info($logPrefix . ($status === ShortUrlConstant::EXPIRED ? 'Expired' : '') . $message . ' - ' . $shortUrl->id . ' - ' . $originalDomain);
                        } catch (\Throwable $th) {
                            $shortUrl->update([
                                'status' => $status,
                                'remarks' => $remarks,
                                'updated_at' => $now->format('Y-m-d H:i:s'),
                            ]);

                            info($logPrefix . ($status === ShortUrlConstant::EXPIRED ? 'Expired' : '') . $message . ' - ' . $shortUrl->id . ' - ' . $originalDomain);

                            Log::error($th);
                        }
                    }
                });

            $message = 'Valid Domain Check Success';
            $this->createdBy->notify(new ValidDomainCheckSuccessNotification($campaignName, $message));
        } catch (\Throwable $th) {
            Log::error($th);
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
        $campaignName = $this->campaign->name;
        $message = 'Valid Domain Check Failed';
        $this->createdBy->notify(new ValidDomainCheckFailNotification($campaignName, $message));
        Log::info('ValidDomainCheckJob failed...' . $campaignName);
        Log::error($th);
    }
}
