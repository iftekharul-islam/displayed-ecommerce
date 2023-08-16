<?php

namespace App\Http\Resources\VisitorCountByCity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorCountByCityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'city' => $this->city,
            'total_count' => $this->total_count,
        ];
    }
}
