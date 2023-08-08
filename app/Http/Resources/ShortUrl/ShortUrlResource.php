<?php

namespace App\Http\Resources\ShortUrl;

use Illuminate\Http\Request;
use App\Http\Resources\Tld\TldResource;
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
            'original_domain' => $this->original_domain,
            'destination_domain' => $this->destination_domain,
            'short_url' => $this->short_url,
            'url_key' => $this->url_key,
            'expired_at' => $this->expired_at,
            'auto_renewal' => $this->auto_renewal,
            'status' => (int)$this->status,
            'remarks' => $this->remarks,
            'campaign_name' =>  $this->campaign_name,
            'su_tld_name' => $this->su_tld_name,
            'su_tld_price' => $this->su_tld_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
