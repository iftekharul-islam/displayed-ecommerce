<?php

namespace App\Http\Controllers\ExcludedDomain;

use App\Models\Campaign;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ExcludedDomain;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ExcludedDomain\ExcludedDomainExportJob;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Resources\ExcludedDomain\ExcludedDomainResource;
use App\Http\Requests\ExcludedDomain\StoreExcludedDomainRequest;
use App\Http\Requests\ExcludedDomain\UpdateExcludedDomainRequest;

class ExcludedDomainController extends Controller
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
            $domain = @$searchQuery['domain'];
            $campaignId = (int)$request->query('campaignId', -1);

            $data  = ExcludedDomain::query()
                ->with(['campaign:id,name'])
                ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                    $query->where('campaign_id', $campaignId);
                })
                ->when($domain, function ($query, $domain) {
                    $query->where('domain', 'LIKE', "%$domain%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return ExcludedDomainResource::collection($data);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExcludedDomainRequest $request)
    {
        try {
            $validated = $request->validated();

            $filteredDomain = removeHttpOrHttps($validated['domain']);
            $filtered = Arr::except($validated, ['domain']);

            ExcludedDomain::firstOrCreate(
                [
                    'campaign_id' => $validated['campaign_id'],
                    'domain' => $filteredDomain,
                ],
                [
                    ...$filtered,
                    'domain' => $filteredDomain,
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
    public function update(UpdateExcludedDomainRequest $request, ExcludedDomain $excludedDomain)
    {
        try {
            $validated = $request->validated();

            $filteredDomain = removeHttpOrHttps($validated['domain']);
            $filtered = Arr::except($validated, ['domain']);

            ExcludedDomain::where([
                'campaign_id' => $validated['campaign_id'],
                'id' => $excludedDomain->id,
            ])->update([
                ...$filtered,
                'domain' => $filteredDomain,
            ]);

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
    public function destroy(string $excludedDomain)
    {
        try {
            ExcludedDomain::destroy($excludedDomain);

            return response()->noContent();
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $request_all = $request->all();
            $sortByKey = data_get($request_all, 'sortByKey', 'id');
            $sortByOrder = data_get($request_all, 'sortByOrder', 'desc');
            $domain = data_get($request_all, 'searchQuery.domain', null);
            $campaignId = (int) data_get($request_all, 'campaignId', -1);

            $getCampaignNameAndSlug = $this->getCampaignNameAndSlug($campaignId);
            $code = Str::random(5);
            $date = now()->format('Y_m_d');
            $exportFileNameSlug = "{$getCampaignNameAndSlug['slug']}Date_{$date}_{$code}.xlsx";

            $exportFilePath = "exports/excluded-domains/export/{$exportFileNameSlug}";
            $exportFileDownloadLink = config('app.url') . "/api/excluded-domains/export/download/{$exportFileNameSlug}";

            $data = [
                'exportedBy' => auth()->user(),
                'exportFileName' => "Exclude Domains : {$getCampaignNameAndSlug['name']}",
                'exportFilePath' => $exportFilePath,
                'exportFileDownloadLink' => $exportFileDownloadLink,
                'sortByKey' => $sortByKey,
                'sortByOrder' => $sortByOrder,
                'domain' => $domain,
                'campaignId' => $campaignId,
            ];

            ExcludedDomainExportJob::dispatch($data);

            return response()->json([
                'message' => 'Excluded domains export started!, please wait...  when done will send you an email',
            ], 200);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function getCampaignNameAndSlug(int $id): array
    {
        if ($id === ShortUrlConstant::ALL) {
            return [
                'name' => 'All Domains',
                'slug' => 'All_Domains_'
            ];
        }

        $campaign = Campaign::findOrFail($id);
        $campaignName = @$campaign->name;
        $campaignNameSlug = Str::slug(@$campaign->name ?? '', '_');

        return [
            'name' => $campaignName,
            'slug' => "{$campaignNameSlug}_"
        ];
    }

    public function exportDownload(string $code)
    {
        try {

            $filePath = "exports/excluded-domains/export/{$code}";

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
}
