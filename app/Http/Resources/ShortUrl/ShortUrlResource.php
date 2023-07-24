<?php

namespace App\Http\Resources\ShortUrl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Campaign\CampaignResource;

class ShortUrlResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tld_id' => $this->tld_id,
            'campaign_id' => $this->campaign_id,
            'original_domain' => $this->original_domain,
            'destination_domain' => $this->destination_domain,
            'short_url' => $this->short_url,
            'url_key' => $this->url_key,
            'tld' => $this->tld,
            'expired_date' => $this->expired_date,
            'auto_renewal' => $this->auto_renewal,
            'status' => $this->status,
            'note' => $this->note,
            'remarks' => $this->remarks,
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
        ];
    }
}
