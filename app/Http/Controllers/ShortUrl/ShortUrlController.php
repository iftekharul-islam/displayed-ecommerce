<?php

namespace App\Http\Controllers\ShortUrl;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use Illuminate\Support\Facades\Cache;
use App\Imports\ShortUrl\ShortUrlImport;
use App\Jobs\ShortUrl\ShortUrlRedirectionJob;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use App\Http\Requests\ShortUrl\ImportShortUrlRequest;
use App\Http\Requests\ShortUrl\UpdateShortUrlRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ShortUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $originalDomain = @$searchQuery['originalDomain'];
            $shortUrl = getCodeFromUrl(@$searchQuery['shortUrl']);
            $tld = @$searchQuery['tld'];
            $campaignId = (int)$request->query('campaignId', -1);

            // filter
            $isFilter = to_boolean($request->query('isFilter', false));

            if ($isFilter) {

                $filterQuery = $request->query('filterQuery');
                $fromDate = @$filterQuery['fromDate'] ? Carbon::make($filterQuery['fromDate'])->format('Y-m-d') : null;
                $toDate = @$filterQuery['toDate'] ? Carbon::make($filterQuery['toDate'])->format('Y-m-d') : null;
                $expireDateFilter = (int)@$filterQuery['expireDateFilter'];
                $statusFilter = (int)@$filterQuery['statusFilter'];
                $tldFilter = @$filterQuery['tldFilter'];

                $data =  ShortUrl::query()
                    ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) use ($fromDate, $toDate) {
                                $query->whereBetween('visit_date', [$fromDate, $toDate])->select(DB::raw('SUM(total_count)'));
                            }
                        ]);
                    })
                    ->when(!$fromDate || !$toDate, function ($query) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) {
                                $query->select(DB::raw('SUM(total_count)'));
                            }
                        ]);
                    })
                    ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                        $query->with([
                            'campaign',
                            'visitorCountByCountries' => function ($query) use ($fromDate, $toDate) {
                                $query->whereBetween('visit_date', [$fromDate, $toDate])->select([
                                    'short_url_id',
                                    'country',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])->groupBy(['short_url_id', 'country'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                        ]);
                    })
                    ->when(!$fromDate || !$toDate, function ($query) {
                        $query->with([
                            'campaign',
                            'visitorCountByCountries' => function ($query) {
                                $query->select([
                                    'short_url_id',
                                    'country',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])->groupBy(['short_url_id', 'country'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                        ]);
                    })
                    ->when($expireDateFilter && $expireDateFilter !== ShortUrlConstant::ALL, function ($query) use ($expireDateFilter) {
                        $query->whereBetween('expired_at', [
                            now()->format('Y-m-d'),
                            now()->addDays($expireDateFilter)->subDay()->format('Y-m-d')
                        ]);
                    })
                    ->when($statusFilter && $statusFilter !== ShortUrlConstant::ALL, function ($query) use ($statusFilter) {
                        $commonConditions = [
                            ['status', $statusFilter],
                            ['expired_at', '>', now()->format('Y-m-d')],
                        ];

                        switch ($statusFilter) {
                            case ShortUrlConstant::EXPIRED:
                                $query->where([
                                    ['status', $statusFilter],
                                    ['expired_at', '<', now()->format('Y-m-d')]
                                ]);
                                break;

                            default:
                                $query->where($commonConditions);
                                break;
                        }
                    })
                    ->when($tldFilter, function ($query) use ($tldFilter) {
                        $query->where('su_tld_name', 'ILIKE', "%$tldFilter%");
                    })
                    ->when($campaignId !== -1, function ($query) use ($campaignId) {
                        $query->where('campaign_id', $campaignId);
                    })
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', $shortUrl);
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', 'ILIKE', "%$originalDomain%");
                    })
                    ->when($tld, function ($query) use ($tld) {
                        $query->where('su_tld_name', 'ILIKE', "%$tld%");
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            } else {
                $data =  ShortUrl::query()
                    ->withCount([
                        'visitorCount as visitor_count' => function ($query) {
                            $query->select(DB::raw('SUM(total_count)'));
                        }
                    ])
                    ->with([
                        'campaign',
                        'visitorCountByCountries' => function ($query) {
                            $query->select([
                                'short_url_id',
                                'country',
                                DB::raw('SUM(total_count) as total_count'),
                            ])->groupBy(['short_url_id', 'country'])
                                ->orderBy('total_count', 'desc')
                                ->limit(5);
                        },
                    ])
                    ->when($campaignId !== -1, function ($query) use ($campaignId) {
                        $query->where('campaign_id', $campaignId);
                    })
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', $shortUrl);
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', 'ILIKE', "%$originalDomain%");
                    })
                    ->when($tld, function ($query) use ($tld) {
                        $query->where('su_tld_name', 'ILIKE', "%$tld%");
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            }

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
            hasPermissionTo(PermissionConstant::SHORT_URLS_CREATE['name']);

            $validated = $request->validated();
            $generatedUrl = config('app.url') . '/vx/';
            $domain = removeHttpOrHttps($validated['original_domain']);
            $extractTld = extractTldFromDomain($domain);
            $code = $generateCodeAction->execute();
            $short_url = $generatedUrl . $code;

            $tldModel = DB::table('tlds')->select(['id'])->where([
                'campaign_id' => $validated['campaign_id'],
                'name' => $extractTld,
            ])->first();

            $excludedDomainsExists = DB::table('excluded_domains')->where([
                'domain' => $domain,
                'campaign_id' => $validated['campaign_id'],
            ])->exists();

            if ($excludedDomainsExists) {
                abort(400, 'This domain is excluded from this campaign');
            }

            ShortUrl::firstOrCreate(
                [
                    'campaign_id' => $validated['campaign_id'],
                    'original_domain' => $domain,
                ],
                [
                    'tld_id' => @$tldModel->id ?? null,
                    'campaign_id' => $validated['campaign_id'],
                    'destination_domain' => $validated['destination_domain'],
                    'short_url' => $short_url,
                    'domain_tld' => $extractTld,
                    'url_key' => $code,
                    'expired_at' => $validated['expired_at'],
                    'auto_renewal' => $validated['auto_renewal'],
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                ]
            );

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
    public function update(UpdateShortUrlRequest $request, ShortUrl $shortUrl)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_EDIT['name']);

            $validated = $request->validated();

            ShortUrl::where([
                'campaign_id' => $validated['campaign_id'],
                'id' => $shortUrl->id,
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
    public function destroy(string $shortUrl)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_DELETE['name']);

            ShortUrl::destroy($shortUrl);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function import(ImportShortUrlRequest $request)
    {
        try {
            $validated = $request->validated();

            if (!$request->hasFile('file') && !$request->file('file')->isValid()) {
                abort(404, 'File not found');
            }

            $file = $request->file('file');

            $campaign = Campaign::findOrFail($validated['campaign_id']);

            (new ShortUrlImport(auth()->user(), $campaign))->queue($file, null, Excel::XLSX);

            return response()->json([
                'message' => 'Short Url import on progress, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function sortUrlRedirection(Request $request, string $code)
    {
        try {
            $short_url = Cache::store('redirection')->rememberForever("redirection:$code", function () use ($code) {
                $short_url =  DB::table('short_urls')
                    ->select('id', 'destination_domain')
                    ->where('url_key', $code)
                    ->first();

                if (empty($short_url)) {
                    abort(404, 'Page not found');
                }

                return $short_url;
            });

            if (empty($short_url)) {
                abort(404, 'Page not found');
            }

            ShortUrlRedirectionJob::dispatch($short_url->id, $request->ip());

            $domainRedirect = 'https://' . $short_url->destination_domain;

            return redirect()->away($domainRedirect, 301);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
