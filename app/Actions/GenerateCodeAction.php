<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateCodeAction
{
    public function execute(): string
    {
        try {
            $code = Str::random(16);

            $existingRecord = DB::table('sort_urls')->where('url_code', $code)->exists();

            if ($existingRecord) {
                $this->execute();
            }

            return $code;
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
