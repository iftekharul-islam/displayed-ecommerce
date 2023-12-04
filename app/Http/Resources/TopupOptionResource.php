<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class TopupOptionResource extends JsonResource
{
    public function toArray($request)
    {
        $user = null;
        if ($request->token)
        {
            try {
                if (!$user = JWTAuth::parseToken()->authenticate()) {
                    return $this->responseWithError(__('unauthorized_user'), [], 401);
                }
            } catch (\Exception $e) {
            }
        }
        return [
            'id'            => $this->id,
            'telecom'       => $this->telecom,
            'value'     => $this->facevalue,
            'description'   => $this->description,
            'type'          => $this->type,
           
        ];
    }
}
