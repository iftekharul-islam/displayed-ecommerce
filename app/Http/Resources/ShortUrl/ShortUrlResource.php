<?php

namespace App\Http\Resources\ShortUrl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Campaign\CampaignResource;
use App\Http\Resources\VisitorCountByCity\VisitorCountByCityResource;
use App\Http\Resources\VisitorCountByCountry\VisitorCountByCountryResource;

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
            'auto_renewal' => (bool)$this->auto_renewal,
            'status' =>  getShortUrlStatus((int) $this->status, $this->expired_at),
            'remarks' => $this->remarks,
            'tld_name' => $this->tld_name,
            'tld_price' => $this->tld_price,
            'campaign' =>  $this->whenLoaded('campaign', function () {
                return new CampaignResource($this->campaign);
            }),
            'visitor_count' => $this->whenCounted('visitorCount', function () {
                return $this->visitor_count;
            }),
            'visitor_count_by_countries' => $this->whenLoaded('visitorCountByCountries', function () {
                return VisitorCountByCountryResource::collection($this->visitorCountByCountries);
            }),
            'visitor_count_by_cities' => $this->whenLoaded('visitorCountByCities', function () {
                return VisitorCountByCityResource::collection($this->visitorCountByCities);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
