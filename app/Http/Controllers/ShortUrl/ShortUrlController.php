<?php

namespace App\Http\Controllers\ShortUrl;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Models\ShortUrl;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Actions\GenerateCodeAction;
use App\Actions\ProIpApi;
use App\Constants\ShortUrlConstant;
use App\Jobs\ShortUrl\TldUpdateJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Imports\ShortUrl\ShortUrlImport;
use App\Jobs\ShortUrl\ShortUrlExportJob;
use App\Jobs\ShortUrl\ValidDomainCheckJob;
use App\Exports\ShortUrl\latestDomainExport;
use App\Jobs\ShortUrl\InvalidDomainCheckJob;
use App\Jobs\ShortUrl\ShortUrlAfterResponseJob;
use App\Http\Requests\ShortUrl\TldUpdateRequest;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use App\Http\Requests\ShortUrl\IndexShortUrlRequest;
use App\Http\Requests\ShortUrl\StoreShortUrlRequest;
use App\Http\Requests\ShortUrl\ImportShortUrlRequest;
use App\Http\Requests\ShortUrl\UpdateShortUrlRequest;
use App\Http\Requests\ShortUrl\ValidDomainCheckRequest;
use App\Http\Requests\ShortUrl\LatestDomainExportRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Jobs\ShortUrl\NotifyUserOfCompletedLatestDomainExportJob;
use App\Models\MasterSetting;
use App\Models\ShortUrlType;

class ShortUrlController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(IndexShortUrlRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URLS_ACCESS['name']);

            $request_all = $request->all();
            $perPage = data_get($request_all, 'perPage', config('app.per_page'));
            $sortByKey = data_get($request_all, 'sortByKey', 'id');
            $sortByOrder = data_get($request_all, 'sortByOrder', 'desc');
            $originalDomain = data_get($request_all, 'searchQuery.originalDomain', null);
            $tld = data_get($request_all, 'searchQuery.tld', null);
            $campaignId = (int) data_get($request_all, 'campaignId', -1);
            $shortUrlInput = data_get($request_all, 'searchQuery.shortUrl', null);
            $shortUrl = getCodeFromUrl($shortUrlInput) ?? null;


            $getCampaignNameAndLastUpdatedDate = $this->getCampaignNameAndLastUpdatedDate($campaignId);

            // filter
            $isFilterInput = data_get($request_all, 'isFilter', false);
            $isFilter = to_boolean($isFilterInput);

            if ($isFilter) {

                $fromDateFilterInput = data_get($request_all, 'filterQuery.fromDateFilter', null);
                $fromDateFilter = $fromDateFilterInput ? Carbon::make($fromDateFilterInput)->format('Y-m-d') : null;
                $toDateFilterInput = data_get($request_all, 'filterQuery.toDateFilter', null);
                $toDateFilter = $toDateFilterInput ? Carbon::make($toDateFilterInput)->format('Y-m-d') : null;
                $expireAtFilter = (int) data_get($request_all, 'filterQuery.expireAtFilter', ShortUrlConstant::ALL);
                $statusFilter = (array) data_get($request_all, 'filterQuery.statusFilter', []);
                $tldFilter = data_get($request_all, 'filterQuery.tldFilter', null);

                $getTrafficDataFiltering = $this->getTrafficDataFiltering($fromDateFilter, $toDateFilter);
                $getExpiryAtFiltering = $this->getExpiryAtFiltering($expireAtFilter);
                $getStatusFiltering = $this->getStatusFiltering($statusFilter);

                $concat_filtering = "Traffic Data Filter : {$getTrafficDataFiltering} | Expire In : {$getExpiryAtFiltering} | Status : {$getStatusFiltering}";

                $data =  ShortUrl::query()
                    ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                                    ->select(DB::raw('COALESCE(SUM(total_count), 0)'));
                            }
                        ]);
                    })
                    ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                        $query->withCount([
                            'visitorCount as visitor_count' => function ($query) {
                                $query->select(DB::raw('COALESCE(SUM(total_count), 0)'));
                            }
                        ]);
                    })
                    ->when($fromDateFilter && $toDateFilter, function ($query) use ($fromDateFilter, $toDateFilter) {
                        $query->with([
                            'campaign:id,name',
                            'type:id,name',
                            'visitorCountByCountries' => function ($query) use ($fromDateFilter, $toDateFilter) {
                                $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                                    ->select([
                                        'short_url_id',
                                        'country',
                                        DB::raw('SUM(total_count) as total_count')
                                    ])
                                    ->whereNotNull('country')
                                    ->groupBy(['short_url_id', 'country'])
                                    ->limit(5);
                            },
                            // 'visitorCountByCities' => function ($query) use ($fromDateFilter, $toDateFilter) {
                            //     $query->whereBetween('visited_at', [$fromDateFilter, $toDateFilter])
                            //         ->select([
                            //             'short_url_id',
                            //             'city',
                            //             DB::raw('SUM(total_count) as total_count')
                            //         ])
                            //         ->whereNotNull('city')
                            //         ->groupBy(['short_url_id', 'city'])
                            //         ->limit(5);
                            // },
                        ]);
                    })
                    ->when(!$fromDateFilter || !$toDateFilter, function ($query) {
                        $query->with([
                            'campaign:id,name',
                            'type:id,name',
                            'visitorCountByCountries' => function ($query) {
                                $query->select([
                                    'short_url_id',
                                    'country',
                                    DB::raw('SUM(total_count) as total_count')
                                ])
                                    ->whereNotNull('country')
                                    ->groupBy(['short_url_id', 'country'])
                                    ->limit(5);
                            },
                            // 'visitorCountByCities' => function ($query) {
                            //     $query->select([
                            //         'short_url_id',
                            //         'city',
                            //         DB::raw('SUM(total_count) as total_count')
                            //     ])
                            //         ->whereNotNull('city')
                            //         ->groupBy(['short_url_id', 'city'])
                            //         ->limit(5);
                            // },
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
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', 'LIKE', "%$shortUrl%");
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', 'LIKE', "%$originalDomain%");
                    })
                    ->when($tldFilter, function ($query) use ($tldFilter) {
                        $query->where('tld_name', $tldFilter);
                    })
                    ->when(!$tldFilter && $tld, function ($query) use ($tld) {
                        $query->where('tld_name', $tld);
                    })
                    ->orderBy($sortByKey, $sortByOrder)
                    ->paginate($perPage);
            } else {

                $concat_filtering = "Traffic Data Filter : All | Expire In : All | Status : Valid, Invalid, Expired";

                $data = ShortUrl::query()
                    ->withCount([
                        'visitorCount as visitor_count' => function ($query) {
                            $query->select(DB::raw('COALESCE(SUM(total_count), 0)'));
                        }
                    ])
                    ->with([
                        'campaign:id,name',
                        'type:id,name',
                        'visitorCountByCountries' => function ($query) {
                            $query->select([
                                'short_url_id',
                                'country',
                                DB::raw('SUM(total_count) as total_count')
                            ])
                                ->whereNotNull('country')
                                ->groupBy(['short_url_id', 'country'])
                                ->limit(5);
                        },
                        // 'visitorCountByCities' => function ($query) {
                        //     $query->select([
                        //         'short_url_id',
                        //         'city',
                        //         DB::raw('SUM(total_count) as total_count')
                        //     ])
                        //         ->whereNotNull('city')
                        //         ->groupBy(['short_url_id', 'city'])
                        //         ->limit(5);
                        // },
                    ])
                    ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                        $query->where('campaign_id', $campaignId);
                    })
                    ->when($shortUrl, function ($query) use ($shortUrl) {
                        $query->where('url_key', 'LIKE', "%$shortUrl%");
                    })
                    ->when($originalDomain, function ($query) use ($originalDomain) {
                        $query->where('original_domain', 'LIKE', "%$originalDomain%");
                    })
                    ->when($tld, function ($query) use ($tld) {
                        $query->where('tld_name', $tld);
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
                    'type_id' => $validated['type_id'] ?? null,
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
            $short_url = ShortUrl::with('type')->where('url_key', $code)->first();

            if (empty($short_url)) {
                abort(404, "Page not found for code: $code");
            }

            $data = [
                'short_url_id' => $short_url->id,
                'request_ip' => $request->ip(),
                'current_date' => now()->format('Y-m-d'),
            ];

            ShortUrlAfterResponseJob::dispatchAfterResponse($data, new Agent());

            $redirection_type = MasterSetting::first()->redirection_type;
            $url = null;

            if ($redirection_type == 1) {
                $url = 'https://' . $short_url->destination_domain;
            } elseif ($redirection_type == 2) {
                if (isset($short_url->type)) {
                    $url = $short_url->type->redirect_url;
                } else {
                    $url = ShortUrlType::where('is_default', true)->first()->redirect_url;
                }
            } elseif ($redirection_type == 3) {
                if ($position = ProIpApi::location($request->ip())) {
                    $country = @$position['country'];
                    info($country);
                }
                $url = 'https://' . $short_url->destination_domain;
            } else {
                $url = 'https://lotto60.com/';
            }

            if($code == '649932fc131a9'){
                return view('hold_for_count', compact('url'));
            }

            return view('redirect', compact('url'));

            // return redirect()
            //     ->away($url, 301, [
            //         'Cache-Control' => 'no-cache, no-store, must-revalidate',
            //         'Pragma' => 'no-cache',
            //         'Expires' => '0',
            //     ]);

        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::channel('redirection')->error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {

            $request_all = $request->all();
            $sortByKey = data_get($request_all, 'sortByKey', 'id');
            $sortByOrder = data_get($request_all, 'sortByOrder', 'desc');
            $originalDomain = data_get($request_all, 'searchQuery.originalDomain', null);
            $tld = data_get($request_all, 'searchQuery.tld', null);
            $campaignId = (int) data_get($request_all, 'campaignId', -1);
            $shortUrlInput = data_get($request_all, 'searchQuery.shortUrl', null);
            $shortUrl = getCodeFromUrl($shortUrlInput) ?? null;

            // filter
            $isFilterInput = data_get($request_all, 'isFilter', false);
            $isFilter = to_boolean($isFilterInput);
            $fromDateFilterInput = data_get($request_all, 'filterQuery.fromDateFilter', null);
            $fromDateFilter = $fromDateFilterInput ? Carbon::make($fromDateFilterInput)->format('Y-m-d') : null;
            $toDateFilterInput = data_get($request_all, 'filterQuery.toDateFilter', null);
            $toDateFilter = $toDateFilterInput ? Carbon::make($toDateFilterInput)->format('Y-m-d') : null;
            $expireAtFilter = (int) data_get($request_all, 'filterQuery.expireAtFilter', ShortUrlConstant::ALL);
            $statusFilter = (array) data_get($request_all, 'filterQuery.statusFilter', []);
            $tldFilter = data_get($request_all, 'filterQuery.tldFilter', null);

            // filter slug for file name
            $getTrafficDataFilteringSlug = $this->getTrafficDataFilteringSlug($fromDateFilter, $toDateFilter);
            $getExpiryAtFilteringSlug = $this->getExpiryAtFilteringSlug($expireAtFilter);
            $getStatusFilteringSlug = $this->getStatusFilteringSlug($statusFilter);
            $getCampaignNameAndLastUpdatedDateSlug = $this->getCampaignNameAndLastUpdatedDateSlug($campaignId);
            $code = Str::random(5);
            $date = now()->format('Y_m_d');
            $exportFileName = "{$getCampaignNameAndLastUpdatedDateSlug}_Traffic_Data_Filter{$getTrafficDataFilteringSlug}Expiry_Date_Filtering{$getExpiryAtFilteringSlug}Status{$getStatusFilteringSlug}Date_{$date}_{$code}.xlsx";

            $user = auth()->user();

            $isExportOriginalDomain =  false;
            if ($user->hasPermissionTo(PermissionConstant::SHORT_URLS_ORIGINAL_DOMAINS_SHOW['name'])) {
                $isExportOriginalDomain =  true;
            }

            $exportFilePath = "exports/short-urls/export/{$exportFileName}";
            $exportFileDownloadLink = config('app.url') . "/api/short-urls/export/download/{$exportFileName}";

            $getCampaignNameAndLastUpdatedDate = $this->getCampaignNameAndLastUpdatedDate($campaignId);
            $getTrafficDataFiltering = $this->getTrafficDataFiltering($fromDateFilter, $toDateFilter);
            $getExpiryAtFiltering = $this->getExpiryAtFiltering($expireAtFilter);
            $getStatusFiltering = $this->getStatusFiltering($statusFilter);

            if ($isFilter) {
                $concatFilterQuery = "{$getCampaignNameAndLastUpdatedDate} | Traffic Data Filter : {$getTrafficDataFiltering} | Expire In : {$getExpiryAtFiltering} | Status : {$getStatusFiltering}";
            } else {
                $concatFilterQuery = "{$getCampaignNameAndLastUpdatedDate} | Traffic Data Filter : All | Expire In : All | Status : Valid, Invalid, Expired";
            }

            $data = [
                'exportedBy' => $user,
                'exportFileName' => $exportFileName,
                'filterQuery' => $concatFilterQuery,
                'campaignId' => $campaignId,
                'fromDateFilter' => $fromDateFilter,
                'toDateFilter' => $toDateFilter,
                'expireAtFilter' => $expireAtFilter,
                'statusFilter' => $statusFilter,
                'tldFilter' => $tldFilter,
                'originalDomain' => $originalDomain,
                'shortUrl' => $shortUrl,
                'tld' => $tld,
                'sortByKey' => $sortByKey,
                'sortByOrder' => $sortByOrder,
                'isExportOriginalDomain' => $isExportOriginalDomain,
                'exportFilePath' => $exportFilePath,
                'exportFileDownloadLink' => $exportFileDownloadLink,
            ];

            ShortUrlExportJob::dispatch($data);

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
                // Download the file
                $file = Storage::disk('public')->get($filePath);
                $response = response()->make($file);

                // Set the appropriate headers for the download
                $headers = [
                    'Content-Type' => Storage::disk('public')->mimeType($filePath),
                    'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
                ];

                // Delete the file
                // Storage::disk('public')->delete($filePath);

                // Return the response with the file contents
                return $response->withHeaders($headers);
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
            $code = Str::random(5);
            $date = now()->format('Y_m_d');
            $exportFileNameSlug = "{$getCampaignNameSlug}{$getDateSlug}_Date_{$date}_{$code}.xlsx";

            $getCampaignNameAndLastUpdatedDate = $this->getCampaignNameAndLastUpdatedDate($campaignId);
            $getTrafficDataFiltering = $this->getTrafficDataFiltering($fromDate, $toDate);

            $concatExportFileName = "{$getCampaignNameAndLastUpdatedDate} | Latest Export Data : {$getTrafficDataFiltering}";

            $exportFilePath = "exports/short-urls/latest-domain-export/{$exportFileNameSlug}";
            $exportFileDownloadLink = config('app.url') . "/api/short-urls/latest-domain-export/download/{$exportFileNameSlug}";

            $data = [
                'exportedBy' => auth()->user(),
                'exportFileName' => $concatExportFileName,
                'exportFileNameSlug' => $exportFileNameSlug,
                'exportFileDownloadLink' => $exportFileDownloadLink,
                'campaignId' => $campaignId,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ];

            (new latestDomainExport($data))->queue($exportFilePath, 'public', Excel::XLSX)->chain([
                new NotifyUserOfCompletedLatestDomainExportJob($data),
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
                // Download the file
                $file = Storage::disk('public')->get($filePath);
                $response = response()->make($file);

                // Set the appropriate headers for the download
                $headers = [
                    'Content-Type' => Storage::disk('public')->mimeType($filePath),
                    'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
                ];

                // Delete the file
                // Storage::disk('public')->delete($filePath);

                // Return the response with the file contents
                return $response->withHeaders($headers);
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
        $campaignName = @$campaign->name;
        $formattedLastUpdatedDate = @$campaign->last_updated_at ? Carbon::make($campaign->last_updated_at)->format('jS F, Y') : null;

        return "{$campaignName} (Last Updated On {$formattedLastUpdatedDate})";
    }

    public function getCampaignNameAndLastUpdatedDateSlug(int $id): string
    {
        if ($id === ShortUrlConstant::ALL) {
            return 'All_Domains_';
        }

        $campaign = Campaign::findOrFail($id);
        $campaignName = @$campaign->name;
        $campaignNameSlug = Str::slug($campaignName ?? '', '_');
        $formattedLastUpdatedDate = @$campaign->last_updated_at ? Carbon::make($campaign->last_updated_at)->format('M_d_Y') : null;

        if ($formattedLastUpdatedDate) {
            return "{$campaignNameSlug}_DB_Updated_On_{$formattedLastUpdatedDate}";
        } else {
            return $campaignNameSlug;
        }
    }

    public function getDateSlug($startDate, $endDate): string
    {
        $formattedStartDate = str_replace([' ', ','], '_', Carbon::make($startDate)->format('M_d_Y'));
        $formattedEndDate = str_replace([' ', ','], '_', Carbon::make($endDate)->format('M_d_Y'));

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
            $formattedStartDate = str_replace([' ', ','], '_', Carbon::make($startDate)->format('M_d_Y'));
            $formattedEndDate = str_replace([' ', ','], '_', Carbon::make($endDate)->format('M_d_Y'));

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
        // if empty then return all
        if (empty($statuses)) {
            return "Valid, Invalid, Expired";
        }

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
        // if empty then return all
        if (empty($statuses)) {
            return "_Valid_Invalid_Expired_";
        }

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
