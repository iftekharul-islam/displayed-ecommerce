<?php

namespace App\Jobs\ShortUrl;

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
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Notifications\ShortUrl\ValidDomainCheckFailNotification;
use App\Notifications\ShortUrl\ValidDomainCheckSuccessNotification;

class InvalidDomainCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailsTo;
    protected $campaign;

    public $timeout = 600;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailsTo, Campaign $campaign)
    {
        $this->mailsTo = $mailsTo;
        $this->campaign = $campaign;

        $this->onQueue('valid-domain-checker-job');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            info("start");
            info(now());
            $campaign = $this->campaign;
            $logPrefix = "ValidDomainCheckJob: {$campaign->name} - ";
            Log::channel('valid-domains-checker')->info("$logPrefix started");

            ShortUrl::query()
                ->select(['id', 'campaign_id', 'original_domain', 'expired_at'])
                ->where([
                    'campaign_id' => $campaign->id,
                    'status' => ShortUrlConstant::INVALID,
                ])
                ->lazyById(1000, 'id')
                ->each(function (ShortUrl $shortUrl) {
                    $now = now();
                    $message = 'Invalid';
                    $remarks = " and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                    $status = ShortUrlConstant::INVALID;

                    $originalDomain = "http://{$shortUrl->original_domain}";

                    if ($shortUrl->expired_at < $now->format('Y-m-d')) {
                        $message = 'Expired';
                        $status = ShortUrlConstant::EXPIRED;
                        $remarks = " and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                    } else {
                        try {
                            info("check start");
                            info(now());
                            $response = Http::withHeaders(['User-Agent' => 'Sajib/DJDJD/0.1'])->get($originalDomain);
                            $responseBody = $response->body();

                            if (preg_match('/<title>(.*?)<\/title>/', $responseBody, $matches)) {
                                $title = $matches[1];
                                $message = 'Valid';

                                if (strpos($title, 'Lotto60') !== false) {
                                    $status = ShortUrlConstant::VALID;
                                    $remarks = " , match Lotto60 and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                                } else if (strpos($title, 'Tickets') !== false) {
                                    $status = ShortUrlConstant::VALID;
                                    $remarks = " , match Tickets and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                                } else {
                                    $message = 'Invalid';
                                    $status = ShortUrlConstant::INVALID;
                                    $remarks = " , not match Lotto60 or Tickets and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                                }
                            }
                            info("check end");
                            info(now());
                        } catch (\Throwable $th) {
                            $message = $th->getMessage();
                        }
                    }
                    info("update start");
                    info(now());
                    $shortUrl->update([
                        'status' => $status,
                        'remarks' => "$message " . $remarks,
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                    info("update end");
                    info(now());
                });

            $message = 'Invalid Domain Check Success';
            Notification::route('mail', $this->mailsTo)->notify(new ValidDomainCheckSuccessNotification($campaign->name, $message));

            info("End");
            info(now());
        } catch (HttpException $th) {
            Log::channel('valid-domains-checker')->error($th->getMessage());
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
        $message = 'Invalid Domain Check Failed';
        Notification::route('mail', $this->mailsTo)->notify(new ValidDomainCheckFailNotification($campaignName, $message));
        Log::channel('valid-domains-checker')->info('InvalidDomainCheck failed...' . $campaignName);
        Log::channel('valid-domains-checker')->error($th);
    }
}
