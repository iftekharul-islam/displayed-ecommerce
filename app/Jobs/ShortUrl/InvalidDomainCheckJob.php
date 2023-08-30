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
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Notifications\ShortUrl\ValidDomainCheckFailNotification;
use App\Notifications\ShortUrl\ValidDomainCheckSuccessNotification;

class InvalidDomainCheckJob implements ShouldQueue
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

            $now = now();
            $message = 'Invalid';
            $remarks = " invalid and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
            $status = ShortUrlConstant::INVALID;

            ShortUrl::query()
                ->select(['id', 'original_domain', 'expired_at'])
                ->where([
                    'campaign_id' => $campaign->id,
                    'status' => ShortUrlConstant::INVALID,
                ])
                ->lazyById(1000, 'id')
                ->each(function (ShortUrl $shortUrl) use ($now, $campaign, $logPrefix, &$message, &$remarks, &$status) {
                    $originalDomain = "http://{$shortUrl->original_domain}";

                    if ($shortUrl->expired_at < $now->format('Y-m-d')) {
                        $message = 'Expired';
                        $status = ShortUrlConstant::EXPIRED;
                        $remarks = " Expired{$shortUrl->id} - $originalDomain and last checked on {$now->format('l')} - {$now->format('F d, Y')}";
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
                        } catch (\Throwable $th) {
                            Log::channel('valid-domains-checker')->error($th);
                        }
                    }

                    $shortUrl->update([
                        'status' => $status,
                        'remarks' => $remarks,
                        'updated_at' => $now->format('Y-m-d H:i:s'),
                    ]);

                    Log::channel('valid-domains-checker')->info("$logPrefix" . ($status === ShortUrlConstant::EXPIRED ? 'Expired' : '') . " $message - $shortUrl->id - $originalDomain");
                });

            $message = 'Valid Domain Check Success';
            Notification::route('mail', $this->mailsTo)->notify(new ValidDomainCheckSuccessNotification($campaign->name, $message));
        } catch (\Throwable $th) {
            Log::channel('valid-domains-checker')->error($th);
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
        Log::info('InvalidDomainCheck failed...' . $campaignName);
        Log::error($th);
    }
}
