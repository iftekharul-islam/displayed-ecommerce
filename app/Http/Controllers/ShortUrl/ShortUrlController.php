<?php

namespace App\Http\Controllers\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use App\Http\Requests\ShortUrl\UpdateShortUrlRequest;
use App\Models\Tld;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ShortUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $originalDomain = @$searchQuery['originalDomain'];
            $tld = @$searchQuery['tld'];
            $campaignId = (int)$request->query('campaignId', -1);

            $query  = ShortUrl::query()->with(['campaign:id,name', 'tld:id,name,price']);

            $query->when($campaignId != -1, function ($query, $campaignId) {
                $query->where('campaign_id', $campaignId);
            });

            $query->when($originalDomain, function ($query, $originalDomain) {
                $query->where('original_domain', 'ILIKE', "%$originalDomain%");
            });

            $query->when($tld, function ($query, $tld) {
                $query->whereHas('tld', function ($query) use ($tld) {
                    $query->where('name', 'ILIKE', "%$tld%");
                });
            });

            $query->orderBy($sortByKey, $sortByOrder);

            $data = $query->paginate($perPage);

            return ShortUrlResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
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
                    $extractTld = extractTldFromDomain($domain);
                    $code = $generateCodeAction->execute();
                    $short_url = $generatedUrl . $code;

                    $tld = Tld::select(['id'])->where([
                        'campaign_id' => $validated['campaign_id'],
                        'name' => $extractTld,
                    ])->first();

                    ShortUrl::firstOrCreate(
                        [
                            'campaign_id' => $validated['campaign_id'],
                            'original_domain' => $domain,
                        ],
                        [
                            'tld_id' => @$tld->id ?? null,
                            'campaign_id' => $validated['campaign_id'],
                            'destination_domain' => $validated['destination_domain'],
                            'short_url' => $short_url,
                            'url_key' => $code,
                            'expired_at' => $originalDomain['expired_at'],
                            'auto_renewal' => $originalDomain['auto_renewal'],
                            'status' => $originalDomain['status'],
                            'remarks' => $validated['remarks'],
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
    public function update(UpdateShortUrlRequest $request, string $id)
    {
        try {
            $validated = $request->validated();

            ShortUrl::where([
                'campaign_id' => $validated['campaign_id'],
                'id' => $id,
            ])->update($validated);

            return response()->json([
                'message' => 'Successfully updated',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            ShortUrl::destroy($id);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
