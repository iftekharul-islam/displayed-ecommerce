<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class ProIpApi
{
    public static function location($ip)
    {
        try {
            $response = Http::get("https://pro.ip-api.com/json/{$ip}", [
                'key' => config('app.ip_api_token_key'),
                'fields' => 'status,message,query,country,city'
            ]);

            $jsonResponse = $response->json();

            if ($jsonResponse['status'] === 'success') {
                return $jsonResponse;
            }

            return false;
        } catch (HttpException $th) {
            Log::channel('redirection')->error($th->getMessage());
            return false;
        }
    }
}
