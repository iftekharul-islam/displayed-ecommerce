<?php

namespace App\Http\Resources\ExcludedDomain;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Campaign\CampaignResource;

class ExcludedDomainResource extends JsonResource
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
            'campaign_id' => $this->campaign_id,
            'domain' => $this->domain,
            'expired_at' => $this->expired_at,
            'auto_renewal' => (bool)$this->auto_renewal,
            'status' => (int)$this->status,
            'note' => $this->note,
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}