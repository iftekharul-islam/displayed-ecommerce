<?php

namespace App\Http\Controllers\ExcludedDomain;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\ExcludedDomain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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

            $query  = ExcludedDomain::query()->with(['campaign:id,name']);

            $query->when($campaignId != -1, function ($query) use ($campaignId) {
                $query->where('campaign_id', $campaignId);
            });

            $query->when($domain, function ($query, $domain) {
                $query->where('domain', 'LIKE', "%$domain%");
            });

            $query->orderBy($sortByKey, $sortByOrder);

            $data = $query->paginate($perPage);

            return ExcludedDomainResource::collection($data);
        } catch (HttpException $th) {
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
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
