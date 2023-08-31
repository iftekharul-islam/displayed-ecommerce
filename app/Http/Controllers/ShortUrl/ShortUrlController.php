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
use App\Jobs\ShortUrl\TldUpdateJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Exports\ShortUrl\ShortUrlExport;
use App\Imports\ShortUrl\ShortUrlImport;
use App\Jobs\ShortUrl\ValidDomainCheckJob;
use App\Exports\ShortUrl\latestDomainExport;
use App\Jobs\ShortUrl\InvalidDomainCheckJob;
use App\Jobs\ShortUrl\ShortUrlRedirectionJob;
use App\Http\Requests\ShortUrl\TldUpdateRequest;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use App\Http\Requests\ShortUrl\ImportShortUrlRequest;
use App\Http\Requests\ShortUrl\UpdateShortUrlRequest;
use App\Jobs\ShortUrl\NotifyUserOfCompletedExportJob;
use App\Http\Requests\ShortUrl\ValidDomainCheckRequest;
use App\Http\Requests\ShortUrl\LatestDomainExportRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Jobs\ShortUrl\NotifyUserOfCompletedLatestDomainExportJob;
use Jenssegers\Agent\Agent;

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

            $getCampaignNameAndLastUpdatedDate = $this->getCampaignNameAndLastUpdatedDate($campaignId);

            // filter
            $isFilter = to_boolean($request->query('isFilter', false));

            if ($isFilter) {

                $filterQuery = $request->query('filterQuery', []);
                $fromDateFilter = @$filterQuery['fromDateFilter'] ? Carbon::make($filterQuery['fromDateFilter'])->format('Y-m-d') : null;
                $toDateFilter = @$filterQuery['toDateFilter'] ? Carbon::make($filterQuery['toDateFilter'])->format('Y-m-d') : null;
                $expireAtFilter = @$filterQuery['expireAtFilter'] ? (int)@$filterQuery['expireAtFilter'] : ShortUrlConstant::ALL;
                $statusFilter = @$filterQuery['statusFilter'] && is_array(@$filterQuery['statusFilter']) ? (array) $filterQuery['statusFilter'] : null;
                $tldFilter = @$filterQuery['tldFilter'] ?? null;

                $getTrafficDataFiltering = $this->getTrafficDataFiltering($fromDateFilter, $toDateFilter);
                $getExpiryAtFiltering = $this->getExpiryAtFiltering($expireAtFilter);
                $getStatusFiltering = $this->getStatusFiltering($statusFilter);

                $concat_filtering = "Traffic Data Filter : {$getTrafficDataFiltering} | Expire In : {$getExpiryAtFiltering} | Status : {$getStatusFiltering}";


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
                                ])
                                    ->whereNotNull('country')
                                    ->groupBy(['short_url_id', 'country'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                            'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])->select([
                                    'short_url_id',
                                    'city',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])
                                    ->whereNotNull('city')
                                    ->groupBy(['short_url_id', 'city'])
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
                                ])
                                    ->whereNotNull('country')
                                    ->groupBy(['short_url_id', 'country'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit(5);
                            },
                            'visitorCountByCities' => function ($query) {
                                $query->select([
                                    'short_url_id',
                                    'city',
                                    DB::raw('SUM(total_count) as total_count'),
                                ])
                                    ->whereNotNull('city')
                                    ->groupBy(['short_url_id', 'city'])
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
                    ->whereHas('campaign', function ($query) {
                        $query->where([
                            'is_active' => true,
                        ]);
                    })
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', $shortUrl);
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', $originalDomain);
                    })
                    ->when($tldFilter, function ($query) use ($tldFilter) {
                        $query->where('tld_name', 'LIKE', "%$tldFilter%");
                    })
                    ->when(!$tldFilter && $tld, function ($query) use ($tld) {
                        $query->where('tld_name', 'LIKE', "%$tld%");
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            } else {

                $concat_filtering = "Traffic Data Filter : All | Expire In : All | Status : Valid, Invalid, Expired";

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
                            ])->whereNotNull('country')
                                ->groupBy(['short_url_id', 'country'])
                                ->orderBy('total_count', 'desc')
                                ->limit(5);
                        },
                        'visitorCountByCities' => function ($query) {
                            $query->select([
                                'short_url_id',
                                'city',
                                DB::raw('SUM(total_count) as total_count'),
                            ])
                                ->whereNotNull('city')
                                ->groupBy(['short_url_id', 'city'])
                                ->orderBy('total_count', 'desc')
                                ->limit(5);
                        },
                    ])
                    ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                        $query->where('campaign_id', $campaignId);
                    })
                    ->whereHas('campaign', function ($query) {
                        $query->where([
                            'is_active' => true,
                        ]);
                    })
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', $shortUrl);
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', $originalDomain);
                    })
                    ->when($tld, function ($query) use ($tld) {
                        $query->where('tld_name', 'LIKE', "%$tld%");
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            }

            return ShortUrlResource::collection($data)
                ->additional(['additional_params' => [
                    'filter_description' => "{$getCampaignNameAndLastUpdatedDate} | {$concat_filtering}"
                ]]);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
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
                    'campaign_id' => $validated['campaign_id'],
                    'destination_domain' => $validated['destination_domain'],
                    'short_url' => $short_url,
                    'tld_name' => $extractTld,
                    'tld_price' => @$tldModel->price ?? null,
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
            logExceptionInSlack($th);
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
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShortUrl $shortUrl)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_DELETE['name']);

            $code = $shortUrl->url_key;
            if (Cache::store('redirection')->has("redirection:$code")) {
                Cache::store('redirection')->forget("redirection:$code");
            }

            $shortUrl->delete();

            return response()->noContent();
        } catch (HttpException $th) {
            logExceptionInSlack($th);
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
                'message' => 'Short Urls import on progress, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
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

            $agent = new Agent();
            $browser = $agent->browser();
            $platform = $agent->platform();
            $deviceType = "Unknown";

            $operating_system_version = $agent->version($platform);
            $browser_version = $agent->version($browser);

            if ($agent->isDesktop()) {
                $deviceType = "Desktop";
            }
            if ($agent->isMobile()) {
                $deviceType = "Mobile";
            }
            if ($agent->isTablet()) {
                $deviceType = "Tablet";
            }
            if ($agent->isRobot()) {
                $deviceType = "Robot";
            }

            $data = [
                'short_url_id' => $short_url->id,
                'request_ip' => $request->ip(),
                'current_date' => now()->format('Y-m-d'),
                'operating_system' => $platform,
                'operating_system_version' => $operating_system_version,
                'browser'                  => $browser,
                'browser_version'          => $browser_version,
                'device_type'              => $deviceType,
            ];

            ShortUrlRedirectionJob::dispatch($data);

            return redirect()->away('https://' . $short_url->destination_domain, 301);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::channel('redirection')->error($th);
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
            $expireAtFilter =  (int)@$filterQuery['expireAtFilter'];
            $statusFilter = @$filterQuery['statusFilter'] && is_array(@$filterQuery['statusFilter']) ? (array) $filterQuery['statusFilter'] : null;
            $tldFilter = @$filterQuery['tldFilter'] ?? null;
            $searchQuery = $request->query('searchQuery', []);
            $originalDomain = @$searchQuery['originalDomain'] ?? null;
            $shortUrl = getCodeFromUrl(@$searchQuery['shortUrl']) ?? null;
            $tld = @$searchQuery['tld'] ?? null;

            $getTrafficDataFilteringSlug = $this->getTrafficDataFilteringSlug($fromDateFilter, $toDateFilter);
            $getExpiryAtFilteringSlug = $this->getExpiryAtFilteringSlug($expireAtFilter);
            $getStatusFilteringSlug = $this->getStatusFilteringSlug($statusFilter);
            $getCampaignNameAndLastUpdatedDateSlug = $this->getCampaignNameAndLastUpdatedDateSlug($campaignId);
            $code = Str::random(10);
            $date = now()->format('Y_m_d_H_i_s');
            $exportFileName = "{$getCampaignNameAndLastUpdatedDateSlug}_Traffic_Data_Filter{$getTrafficDataFilteringSlug}Expiry_Date_Filtering{$getExpiryAtFilteringSlug}Status{$getStatusFilteringSlug}Date_{$date}_{$code}.xlsx";

            $data = [
                'exportFileName' => $exportFileName,
                'campaignId' => $campaignId,
                'fromDateFilter' => $fromDateFilter,
                'toDateFilter' => $toDateFilter,
                'expireAtFilter' => $expireAtFilter,
                'statusFilter' => $statusFilter,
                'tldFilter' => $tldFilter,
                'originalDomain' => $originalDomain,
                'shortUrl' => $shortUrl,
                'tld' => $tld,
            ];

            $user = auth()->user();

            $isExportOriginalDomain =  false;
            if ($user->hasPermissionTo(PermissionConstant::SHORT_URLS_ORIGINAL_DOMAINS_SHOW['name'])) {
                $isExportOriginalDomain =  true;
            }

            $exportFilePath = "exports/short-urls/export/{$exportFileName}";
            $exportFileDownloadLink = config('app.url') . "/api/short-urls/export/download/{$exportFileName}";

            (new ShortUrlExport($user, $data, $isExportOriginalDomain))->queue($exportFilePath, 'public', Excel::XLSX)->chain([
                new NotifyUserOfCompletedExportJob($user, $exportFileName, $exportFileDownloadLink),
            ]);

            return response()->json([
                'message' => 'Short urls export started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function exportDownload(string $code)
    {
        try {
            $filePath = "exports/short-urls/export/{$code}";

            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            }

            abort(404, 'File not found');
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function latestDomainExport(LatestDomainExportRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_LATEST_DOMAINS_EXPORT['name']);

            $validated = $request->validated();
            $campaignId = (int)$validated['campaignId'];
            $fromDate =  Carbon::make($validated['fromDate'])->format('Y-m-d');
            $toDate = Carbon::make($validated['toDate'])->format('Y-m-d');

            $getCampaignNameSlug = $this->getCampaignNameSlug($campaignId);
            $getDateSlug = $this->getDateSlug($fromDate, $toDate);
            $code = Str::random(10);
            $date = now()->format('Y_m_d_H_i_s');
            $exportFileName = "{$getCampaignNameSlug}{$getDateSlug}_Date_{$date}_{$code}.xlsx";

            $exportFilePath = "exports/short-urls/latest-domain-export/{$exportFileName}";
            $exportFileDownloadLink = config('app.url') . "/api/short-urls/latest-domain-export/download/{$exportFileName}";

            $data = [
                'exportFileName' => $exportFileName,
                'campaignId' => $campaignId,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ];

            $user = auth()->user();

            (new latestDomainExport($user, $data))->queue($exportFilePath, 'public', Excel::XLSX)->chain([
                new NotifyUserOfCompletedLatestDomainExportJob($user, $exportFileName, $exportFileDownloadLink),
            ]);

            return response()->json([
                'message' => 'Short Urls latest domain export started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function latestDomainExportDownload(string $code)
    {
        try {
            $filePath = "exports/short-urls/latest-domain-export/{$code}";

            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            }

            abort(404, 'File not found');
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function tldUpdate(TldUpdateRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_TLD_UPDATE['name']);

            $validated = $request->validated();

            $user = auth()->user();
            $campaign = Campaign::findOrFail($validated['campaignId']);

            TldUpdateJob::dispatch($user, $campaign);

            return response()->json([
                'message' => 'Short Urls tld update started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function getCampaignNameSlug(int $id): string
    {
        $campaign = Campaign::findOrFail($id);
        $campaignNameSlug = Str::slug($campaign->name ?? '', '_');

        return $campaignNameSlug;
    }

    public function getCampaignNameAndLastUpdatedDate(int $id): string
    {
        if ($id === ShortUrlConstant::ALL) {
            return "All Domains";
        }

        $campaign = Campaign::findOrFail($id);
        $campaignName = $campaign->name;
        $formattedLastUpdatedDate = $campaign->last_updated_at ? Carbon::make($campaign->last_updated_at)->format('jS F, Y') : null;

        return "{$campaignName} (Last Updated On {$formattedLastUpdatedDate})";
    }

    public function getCampaignNameAndLastUpdatedDateSlug(int $id): string
    {
        if ($id === ShortUrlConstant::ALL) {
            return "ALL";
        }

        $campaign = Campaign::findOrFail($id);
        $campaignNameSlug = Str::slug($campaign->name ?? '', '_');
        $formattedLastUpdatedDate = $campaign->last_updated_at ? Carbon::make($campaign->last_updated_at)->format('F_d_Y') : null;

        if ($formattedLastUpdatedDate) {
            return "{$campaignNameSlug}_Database_Updated_On_{$formattedLastUpdatedDate}";
        } else {
            return $campaignNameSlug;
        }
    }

    public function getDateSlug($startDate, $endDate): string
    {
        $formattedStartDate = str_replace([' ', ','], '_', Carbon::make($startDate)->format('F_d_Y'));
        $formattedEndDate = str_replace([' ', ','], '_', Carbon::make($endDate)->format('F_d_Y'));

        return "_{$formattedStartDate}_To_{$formattedEndDate}_";
    }

    public function getTrafficDataFiltering($startDate, $endDate): string
    {
        if (!empty($startDate) && !empty($endDate)) {
            $formattedStartDate = Carbon::make($startDate)->format('jS F, Y');
            $formattedEndDate = Carbon::make($endDate)->format('jS F, Y');

            return "{$formattedStartDate} To {$formattedEndDate}";
        }

        return "All";
    }

    public function getTrafficDataFilteringSlug($startDate, $endDate): string
    {
        if (!empty($startDate) && !empty($endDate)) {
            $formattedStartDate = str_replace([' ', ','], '_', Carbon::make($startDate)->format('F_d_Y'));
            $formattedEndDate = str_replace([' ', ','], '_', Carbon::make($endDate)->format('F_d_Y'));

            return "_{$formattedStartDate}_To_{$formattedEndDate}_";
        }

        return "_All_";
    }

    public function getExpiryAtFiltering(int $filtering): string
    {
        $filterMap = [
            ShortUrlConstant::EXPIRED_NEXT_THREE_DAYS => "3 days",
            ShortUrlConstant::EXPIRED_NEXT_SEVEN_DAYS => "Next 7 days",
            ShortUrlConstant::EXPIRED_NEXT_FIFTEEN_DAYS => "Next 15 days",
            ShortUrlConstant::EXPIRED_NEXT_ONE_MONTH => "Next One month",
            ShortUrlConstant::EXPIRED_NEXT_THREE_MONTHS => "Next Three months",
            ShortUrlConstant::EXPIRED_NEXT_SIX_MONTHS => "Next Six months",
            ShortUrlConstant::ALL => "All",
        ];

        return $filterMap[$filtering];
    }

    public function getExpiryAtFilteringSlug(int $filtering): string
    {
        $filterMap = [
            ShortUrlConstant::EXPIRED_NEXT_THREE_DAYS => "_3_days_",
            ShortUrlConstant::EXPIRED_NEXT_SEVEN_DAYS => "_Next_7_days_",
            ShortUrlConstant::EXPIRED_NEXT_FIFTEEN_DAYS => "_Next_15_days_",
            ShortUrlConstant::EXPIRED_NEXT_ONE_MONTH => "_Next_One_month_",
            ShortUrlConstant::EXPIRED_NEXT_THREE_MONTHS => "_Next_Three_months_",
            ShortUrlConstant::EXPIRED_NEXT_SIX_MONTHS => "_Next_Six_months_",
            ShortUrlConstant::ALL => "_All_",
        ];

        return $filterMap[$filtering];
    }

    public function getStatusFiltering(array $statuses): string
    {
        $statusStrings = [
            ShortUrlConstant::VALID => "Valid",
            ShortUrlConstant::INVALID => "Invalid",
            ShortUrlConstant::EXPIRED => "Expired",
        ];

        $filteredStatuses = array_map(function ($status) use ($statusStrings) {
            return $statusStrings[$status];
        }, $statuses);

        return implode(", ", $filteredStatuses);
    }

    public function getStatusFilteringSlug(array $statuses): string
    {
        $filterMap = [
            ShortUrlConstant::VALID => "_Valid",
            ShortUrlConstant::INVALID => "_Invalid",
            ShortUrlConstant::EXPIRED => "_Expired",
        ];

        return implode("", array_map(function ($status) use ($filterMap) {
            return $filterMap[$status];
        }, $statuses)) . "_";
    }


    public function validDomainCheck(ValidDomainCheckRequest $request)
    {
        try {
            $validated = $request->validated();

            $campaign =  Campaign::where([
                'id' => $validated['campaign_id'],
                'is_active' => true,
            ])->firstOrFail();

            $userEmail = @auth()->user()->email;
            $mailsTo = [$userEmail];

            ValidDomainCheckJob::dispatch($mailsTo, $campaign);

            return response()->json([
                'message' => 'Valid domain check started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function invalidDomainCheck(ValidDomainCheckRequest $request)
    {
        try {
            $validated = $request->validated();

            $campaign =  Campaign::where([
                'id' => $validated['campaign_id'],
                'is_active' => true,
            ])->firstOrFail();

            $userEmail = @auth()->user()->email;
            $mailsTo = [$userEmail];

            InvalidDomainCheckJob::dispatch($mailsTo, $campaign);

            return response()->json([
                'message' => 'Invalid domain check started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
