<?php

namespace App\Jobs\ShortUrl;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Constants\ShortUrlConstant;
use App\Models\ShortUrl;
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

class ValidDomainCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailsTo;
    protected $campaign;


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
            $campaign = $this->campaign;
            $logPrefix = "ValidDomainCheckJob: {$campaign->name} - ";
            Log::channel('valid-domains-checker')->info("$logPrefix started");

            ShortUrl::query()
                ->select(['id', 'campaign_id', 'original_domain', 'expired_at'])
                ->where([
                    'campaign_id' => $campaign->id,
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
                            $response = Http::withHeaders(['User-Agent' => 'Sajib/DJDJD/0.1'])->get($originalDomain);
                            $responseBody = $response->body();

                            if (preg_match('/<title>(.*?)<\/title>/', $responseBody, $matches)) {
                                $title = $matches[1];
                                $message = 'Valid';
                                $status = strpos($title, 'Lotto60') !== false ? ShortUrlConstant::VALID : ShortUrlConstant::INVALID;
                                $remarks = " and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                            } else if (preg_match('/<title>(.*?)<\/title>/', $responseBody, $matches)) {
                                $title = $matches[1];
                                $message = 'Valid';
                                $status = strpos($title, 'Tickets.love') !== false ? ShortUrlConstant::VALID : ShortUrlConstant::INVALID;
                                $remarks = " and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
                            }
                        } catch (\Throwable $th) {
                            $message = $th->getMessage();
                        }
                    }

                    $shortUrl->update([
                        'status' => $status,
                        'remarks' => "$message " . $remarks,
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);
                });

            $message = 'Valid Domain Check Success';
            Notification::route('mail', $this->mailsTo)->notify(new ValidDomainCheckSuccessNotification($campaign->name, $message));
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
        $message = 'Valid Domain Check Failed';
        Notification::route('mail', $this->mailsTo)->notify(new ValidDomainCheckFailNotification($campaignName, $message));
        Log::channel('valid-domains-checker')->info('ValidDomainCheckJob failed...' . $campaignName);
        Log::channel('valid-domains-checker')->error($th);
    }
}
