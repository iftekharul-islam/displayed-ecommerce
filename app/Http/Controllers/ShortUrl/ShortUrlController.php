<?php

namespace App\Http\Controllers\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ShortUrlController extends Controller
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
    public function store(StoreShortUrlRequest $request, GenerateCodeAction $generateCodeAction)
    {
        try {
            $validated = $request->validated();
            $generatedUrl = config('app.url') . '/vx/';

            DB::transaction(function () use ($validated, $generateCodeAction, $generatedUrl) {
                foreach ($validated['original_domains'] as $originalDomain) {
                    $domain = removeHttpOrHttps($originalDomain['domain']);
                    $tld = extractTldFromDomain($domain);
                    $code = $generateCodeAction->execute();
                    $short_url = $generatedUrl . $code;

                    ShortUrl::firstOrCreate(
                        [
                            'original_domain' => $domain,
                            'campaign_id' => $validated['campaign_id'],
                        ],
                        [
                            'campaign_id' => $validated['campaign_id'],
                            'destination_domain' => $validated['destination_domain'],
                            'short_url' => $short_url,
                            'url_key' => $code,
                            'expired_date' => $originalDomain['expired_date'],
                            'auto_renewal' => $originalDomain['auto_renewal'],
                            'status' => $originalDomain['status'],
                            'tld' => $tld,
                            'note' => $validated['note'],
                        ]
                    );
                }
            });

            return response()->json([
                'message' => 'Successfully created',
            ], 201);
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
