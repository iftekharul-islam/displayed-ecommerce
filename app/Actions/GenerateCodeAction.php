<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GenerateCodeAction
{
    public function execute(): string
    {
        try {
            $code = Str::random(16);
            $existingCacheRecord =  Cache::store('redirection')->has("redirection:$code");
            $existingRecord = DB::table('short_urls')->where('url_key', $code)->exists();

            if ($existingCacheRecord || $existingRecord) {
                $this->execute();
            }

            return $code;
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
