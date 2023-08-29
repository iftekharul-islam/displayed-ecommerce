<?php

namespace App\Http\Controllers\Campaign;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use App\Http\Resources\Campaign\CampaignResource;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::CAMPAIGNS_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $name = @$searchQuery['name'];

            $data  = Campaign::query()->withCount(['tlds'])
                ->when($name, function ($query, $name) {
                    $query->where('name', 'LIKE', "%$name%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return CampaignResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCampaignRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::CAMPAIGNS_CREATE['name']);

            $validated = $request->validated();

            Campaign::create($validated);

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
    public function update(UpdateCampaignRequest $request, string $campaign)
    {
        try {
            hasPermissionTo(PermissionConstant::CAMPAIGNS_EDIT['name']);

            $validated = $request->validated();

            $model = Campaign::findOrFail($campaign);

            $model->update($validated);

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
    public function destroy(string $campaign)
    {
        try {
            hasPermissionTo(PermissionConstant::CAMPAIGNS_DELETE['name']);

            Campaign::destroy($campaign);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function actives()
    {
        try {
            $data = Campaign::where('is_active', true)->get();

            return CampaignResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function trashes(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::CAMPAIGNS_SOFT_DELETE_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $name = @$searchQuery['name'];

            $data  = Campaign::query()
                ->onlyTrashed()
                ->withCount(['tlds'])
                ->when($name, function ($query, $name) {
                    $query->where('name', 'LIKE', "%$name%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return CampaignResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function restore($campaign)
    {
        try {
            Campaign::query()->onlyTrashed()->findOrFail($campaign)->restore();

            return response()->json([
                'message' => 'Successfully restored',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function forceDeletes()
    {
        try {
            Campaign::query()->onlyTrashed()->forceDelete();

            return response()->json([
                'message' => 'Successfully deleted',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}