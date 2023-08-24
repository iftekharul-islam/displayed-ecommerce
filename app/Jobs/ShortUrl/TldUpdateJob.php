<?php

namespace App\Jobs\ShortUrl;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Notifications\ShortUrl\TldUpdateFailedNotification;
use App\Notifications\ShortUrl\TldUpdateSuccessNotification;

class TldUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Campaign $campaign)
    {
        $this->user = $user;
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $campaignId = $this->campaign->id;

            DB::table('tlds')
                ->where('campaign_id', $campaignId)
                ->lazyById(1000, 'id')
                ->each(function ($tld) use ($campaignId) {
                    DB::table('short_urls')
                        ->where([
                            'campaign_id' => $campaignId,
                            'tld_name' => $tld->name,
                        ])
                        ->update([
                            'tld_price' => $tld->price,
                        ]);
                });

            $this->user->notify(new TldUpdateSuccessNotification($this->campaign->name));
        } catch (HttpException $th) {
            Log::error($th);
            $this->user->notify(new TldUpdateFailedNotification($this->campaign->name));
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
