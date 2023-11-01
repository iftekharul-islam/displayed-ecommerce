<?php

namespace App\Http\Resources\ShortUrlType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortUrlTypeResource extends JsonResource
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
            'name' => $this->name,
            'is_default' => $this->isDefault,
            'redirect_url' => $this->redirect_url,
            'count' => $this->count,
            'created_at' => $this->created_at,
        ];
    }
}
