<?php

namespace App\Http\Controllers\SortUrl;

use App\Models\SortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Constants\ShortUrlConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\SortUrl\StoreSortUrlRequest;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SortUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSortUrlRequest $request, GenerateCodeAction $generateCodeAction)
    {
        try {
            $validated = $request->validated();
            $generatedUrl = config('app.url') . '/vx/';

            DB::transaction(function () use ($validated, $generateCodeAction, $generatedUrl) {
                foreach ($validated['original_domains'] as $originalDomain) {
                    $url = rtrim(str_replace(['http://', 'https://'], '', $originalDomain['domain']), '/');
                    $tld = extractTld($url);
                    $code = $generateCodeAction->execute();
                    $short_url = $generatedUrl . $code;

                    SortUrl::firstOrCreate(
                        [
                            'original_domain' => $url,
                            'campaign_id' => $validated['campaign_id'],
                        ],
                        [
                            'campaign_id' => $validated['campaign_id'],
                            'destination_domain' => $validated['destination_domain'],
                            'short_url' => $short_url,
                            'url_code' => $code,
                            'expired_date' => $originalDomain['expired_date'],
                            'auto_renewal' => $originalDomain['auto_renewal'],
                            'status' => $originalDomain['status'],
                            'tld' => $tld,
                            'remarks' => $validated['remarks'],
                        ]
                    );
                }
            });
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
