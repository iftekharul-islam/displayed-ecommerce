<?php

namespace App\Http\Controllers\ShortUrl;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Models\ShortUrl;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Exports\ShortUrl\ShortUrlExport;
use App\Imports\ShortUrl\ShortUrlImport;
use App\Jobs\ShortUrl\ShortUrlRedirectionJob;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use App\Http\Requests\ShortUrl\ImportShortUrlRequest;
use App\Http\Requests\ShortUrl\UpdateShortUrlRequest;
use App\Jobs\ShortUrl\NotifyUserOfCompletedExportJob;
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
            $searchQuery = $request->query('searchQuery', []);
            $originalDomain = @$searchQuery['originalDomain'] ?? null;
            $shortUrl = getCodeFromUrl(@$searchQuery['shortUrl']) ?? null;
            $tld = @$searchQuery['tld'] ?? null;
            $campaignId = (int) $request->query('campaignId', -1);

            // filter
            $isFilter = to_boolean($request->query('isFilter', false));

            if ($isFilter) {

                $filterQuery = $request->query('filterQuery', []);
                $fromDateFilter = @$filterQuery['fromDateFilter'] ? Carbon::make($filterQuery['fromDateFilter'])->format('Y-m-d') : null;
                $toDateFilter = @$filterQuery['toDateFilter'] ? Carbon::make($filterQuery['toDateFilter'])->format('Y-m-d') : null;
                $expireAtFilter = @$filterQuery['expireAtFilter'] ? (int)@$filterQuery['expireAtFilter'] : null;
                $statusFilter = @$filterQuery['statusFilter'] && is_array(@$filterQuery['statusFilter']) ? (array) $filterQuery['statusFilter'] : null;
                $tldFilter = @$filterQuery['tldFilter'] ?? null;

                $data =  ShortUrl::query()
                    ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select(DB::raw('SUM(total_count)'));
                            }
                        ]);
                    })
                    ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) {
                                $query->select(DB::raw('SUM(total_count)'));
                            }
                        ]);
                    })
                    ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->with([
                            'campaign',
                            'visitorCountByCountries' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                                    'short_url_id',
                                    'country',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])->groupBy(['short_url_id', 'country'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                            'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                                    'short_url_id',
                                    'city',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])->groupBy(['short_url_id', 'city'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                        ]);
                    })
                    ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
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
                            'visitorCountByCities' => function ($query) {
                                $query->select([
                                    'short_url_id',
                                    'city',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])->groupBy(['short_url_id', 'city'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                        ]);
                    })
                    ->when($expireAtFilter && $expireAtFilter !== ShortUrlConstant::ALL, function ($query) use ($expireAtFilter) {
                        $query->whereBetween('expired_at', [
                            now()->format('Y-m-d'),
                            now()->addDays((int) $expireAtFilter)->subDay()->format('Y-m-d')
                        ]);
                    })
                    ->when($statusFilter, function ($query) use ($statusFilter) {
                        $query->where(function ($subquery) use ($statusFilter) {
                            foreach ($statusFilter as $status) {
                                if ((int)$status === ShortUrlConstant::EXPIRED) {
                                    $subquery->orWhere(function ($expiredSubquery) {
                                        $expiredSubquery->where('status', ShortUrlConstant::EXPIRED)
                                            ->orWhere('expired_at', '<', now()->format('Y-m-d'));
                                    });
                                } else {
                                    $subquery->orWhere(function ($commonSubquery) use ($status) {
                                        $commonSubquery->where('status', (int)$status)
                                            ->where('expired_at', '>', now()->format('Y-m-d'));
                                    });
                                }
                            }
                        });
                    })
                    ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                        $query->where('campaign_id', $campaignId);
                    })
                    ->when($tldFilter, function ($query) use ($tldFilter) {
                        $query->where('su_tld_name', 'ILIKE', "%$tldFilter%");
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            } else {
                $data = ShortUrl::query()
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
                        'visitorCountByCities' => function ($query) {
                            $query->select([
                                'short_url_id',
                                'city',
                                DB::raw('SUM(total_count) as total_count'),
                            ])->groupBy(['short_url_id', 'city'])
                                ->orderBy('total_count', 'desc')
                                ->limit(5);
                        },
                    ])
                    ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
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

            return ShortUrlResource::collection($data)
                ->additional(['filterQuery' => [
                    'key' => 'value',
                ]]);
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
                return DB::table('short_urls')
                    ->select('id', 'destination_domain')
                    ->where('url_key', $code)
                    ->first();
            });

            if (empty($short_url)) {
                abort(404, 'Page not found');
            }

            ShortUrlRedirectionJob::dispatch($short_url->id, $request->ip());

            return redirect()->away('https://' . $short_url->destination_domain, 301);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $campaignId = (int)$request->query('campaignId', -1);
            $filterQuery = $request->query('filterQuery', []);
            $fromDateFilter = @$filterQuery['fromDateFilter'] ? Carbon::make($filterQuery['fromDateFilter'])->format('Y-m-d') : null;
            $toDateFilter = @$filterQuery['toDateFilter'] ? Carbon::make($filterQuery['toDateFilter'])->format('Y-m-d') : null;
            $expireAtFilter = @$filterQuery['expireAtFilter'] ? (int)@$filterQuery['expireAtFilter'] : null;
            $statusFilter = @$filterQuery['statusFilter'] && is_array(@$filterQuery['statusFilter']) ? (array) $filterQuery['statusFilter'] : null;
            $tldFilter = @$filterQuery['tldFilter'] ?? null;

            $campaignName = $this->getCampaignName($campaignId);
            $code = Str::random(10);
            $exportFileName = $campaignName . '_' . now()->format('Y_m_d_H_i_s') . '_' . $code . '.xlsx';

            $data = [
                'exportFileName' => $exportFileName,
                'campaignId' => $campaignId,
                'fromDateFilter' => $fromDateFilter,
                'toDateFilter' => $toDateFilter,
                'expireAtFilter' => $expireAtFilter,
                'statusFilter' => $statusFilter,
                'tldFilter' => $tldFilter,
            ];

            $user = auth()->user();

            $exportFilePath = "exports/short-urls/{$exportFileName}";
            $exportFileDownloadLink = config('app.url') . "/api/short-urls/export/download/{$exportFileName}";

            (new ShortUrlExport($user, $data))->queue($exportFilePath, 'public', Excel::XLSX)->chain([
                new NotifyUserOfCompletedExportJob($user, $exportFileName, $exportFileDownloadLink),
            ]);

            return response()->json([
                'message' => 'Short Url Export started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function download(string $code)
    {
        try {
            $filePath = "exports/short-urls/{$code}";

            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            }

            abort(404, 'File not found');
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function getCampaignName(int $id): string
    {
        if ($id === ShortUrlConstant::ALL) {
            return "ALL";
        }

        return  Campaign::findOrFail($id)->name;
    }

    public function getTrafficDataFilteringSlug($startDate, $endDate)
    {
        if (!empty($startDate) && !empty($endDate)) {
            $formattedStartDate = str_replace([' ', ','], '_', Carbon::make($startDate)->format('F_d_Y'));
            $formattedEndDate = str_replace([' ', ','], '_', Carbon::make($endDate)->format('F_d_Y'));

            return "_{$formattedStartDate}_to_{$formattedEndDate}_";
        }

        return "_All_";
    }

    public function getExpiryAtFilteringSlug($filtering)
    {
        $filterMap = [
            ShortUrlConstant::EXPIRED_NEXT_THREE_DAYS => "_3_days_",
            ShortUrlConstant::EXPIRED_NEXT_SEVEN_DAYS => "_Next_7_days_",
            ShortUrlConstant::EXPIRED_NEXT_FIFTEEN_DAYS => "_Next_15_days_",
            ShortUrlConstant::EXPIRED_NEXT_ONE_MONTH => "_Next_One_month_",
            ShortUrlConstant::EXPIRED_NEXT_THREE_MONTHS => "_Next_Three_months_",
            ShortUrlConstant::ALL => "_All_",
        ];

        return $filterMap[$filtering] ?? "_All_";
    }

    public function getStatusFilteringSlug($status)
    {
        $filterMap = [
            ShortUrlConstant::VALID => "_Valid",
            ShortUrlConstant::INVALID => "_Invalid",
            ShortUrlConstant::EXPIRED => "_Expired",
        ];

        return $filterMap[$status] ?? "_All_";
    }
}
