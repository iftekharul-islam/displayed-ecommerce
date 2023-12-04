<?php

namespace App\Http\Resources\AdminResource;

use Illuminate\Http\Resources\Json\JsonResource;

class PosOfflineMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'image'            => arrayCheck('original_image',$this->thumbnail) && is_file_exists($this->thumbnail['original_image'] ,$this->thumbnail['storage']) ? get_media($this->thumbnail['original_image'] , $this->thumbnail['storage']) :  static_asset('images\default\105x75.png'),
            'instructions'     => $this->instructions,
        ];
    }
}
