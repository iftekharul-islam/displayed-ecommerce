<?php

namespace App\Http\Controllers\ShortUrlType;

use App\Models\ShortUrlType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use App\Http\Resources\ShortUrlType\ShortUrlTypeResource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\ShortUrlType\StoreShortUrlTypeRequest;
use App\Http\Requests\ShortUrlType\UpdateShortUrlTypeRequest;

class ShortUrlTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URL_TYPES_ACCESS['name']);

            $request_all = $request->all();
            $perPage = data_get($request_all, 'perPage', config('app.per_page'));
            $sortByKey = data_get($request_all, 'sortByKey', 'id');
            $sortByOrder = data_get($request_all, 'sortByOrder', 'desc');
            $name = data_get($request_all, 'searchQuery.name', null);

            $data  = ShortUrlType::query()
                ->when($name, function ($query, $name) {
                    $query->where('name', 'LIKE', "%$name%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return ShortUrlTypeResource::collection($data);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShortUrlTypeRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URL_TYPES_CREATE['name']);

            $validated = $request->validated();

            ShortUrlType::create($validated);

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
    public function update(UpdateShortUrlTypeRequest $request, ShortUrlType $short_url_type)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URL_TYPES_EDIT['name']);

            $validated = $request->validated();

            $short_url_type->update($validated);

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
    public function destroy(string $short_url_type)
    {
        try {
            hasPermissionTo(PermissionConstant::SHORT_URL_TYPES_DELETE['name']);

            ShortUrlType::destroy($short_url_type);

            return response()->noContent();
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function all()
    {
        try {
            $data = ShortUrlType::all();

            return ShortUrlTypeResource::collection($data);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
