<?php

namespace App\Http\Resources\Tld;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Campaign\CampaignResource;

class TldResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'last_updated_at' => $this->last_updated_at,
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'created_at' => $this->created_at,
        ];
    }
}
