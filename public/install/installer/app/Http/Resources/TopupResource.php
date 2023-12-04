<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class TopupResource extends JsonResource
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
            'uid'       	=> $this->uid,
            'userid'       	=> $this->user_id,
            'telephone'     => $this->telephone,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
           
        ];
    }
}
