<?php

namespace App\Http\Controllers\Tld;

use App\Models\Tld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tld\TldResource;
use App\Http\Requests\Tld\StoreTldRequest;
use App\Http\Requests\Tld\UpdateTldRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TldController extends Controller
{
    public function get(Request $request, string $campaign_id)
    {
        $perPage = $request->query('perPage', config('app.per_page'));
        $sortByKey = $request->query('sortByKey', 'id');
        $sortByOrder = $request->query('sortByOrder', 'desc');
        $searchQuery = $request->query('searchQuery');
        $name = @$searchQuery['name'];

        $query  = Tld::query()
            ->where('campaign_id', $campaign_id);

        $query->when($name, function ($query, $name) {
            $query->where('name', 'ILIKE', "%$name%");
        });

        $query->orderBy($sortByKey, $sortByOrder);

        $data = $query->paginate($perPage);

        return TldResource::collection($data);
    }

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
    public function store(StoreTldRequest $request, string $campaign_id)
    {
        try {
            $validated = $request->validated();

            Tld::create([
                ...$validated,
                'campaign_id' => $campaign_id,
            ]);

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
    public function update(UpdateTldRequest $request, string $campaign_id, string $id)
    {
        try {
            $validated = $request->validated();

            Tld::where([
                'campaign_id' => $campaign_id,
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
    public function destroy(string $campaign_id, string $id)
    {
        try {
            Tld::destroy($id);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
