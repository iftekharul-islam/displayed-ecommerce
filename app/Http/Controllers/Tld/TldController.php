<?php

namespace App\Http\Controllers\Tld;

use App\Models\Tld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tld\StoreTldRequest;
use App\Http\Requests\Tld\UpdateTldRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TldController extends Controller
{
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
