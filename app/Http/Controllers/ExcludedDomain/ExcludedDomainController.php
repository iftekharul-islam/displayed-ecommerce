<?php

namespace App\Http\Controllers\ExcludedDomain;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\ExcludedDomain;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\ExcludedDomain\StoreExcludedDomainRequest;
use App\Http\Requests\ExcludedDomain\UpdateExcludedDomainRequest;

class ExcludedDomainController extends Controller
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
    public function store(StoreExcludedDomainRequest $request)
    {
        try {
            $validated = $request->validated();

            $filtered = Arr::except($validated, ['domain']);

            ExcludedDomain::create([
                ...$filtered,
                'domain' => removeHttpOrHttps($validated['domain']),
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
    public function update(UpdateExcludedDomainRequest $request, string $id)
    {
        try {
            $validated = $request->validated();

            $filtered = Arr::except($validated, ['domain']);

            ExcludedDomain::where([
                'campaign_id' => $validated['campaign_id'],
                'id' => $id,
            ])->update([
                ...$filtered,
                'domain' => removeHttpOrHttps($validated['domain']),
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
    public function destroy(string $id)
    {
        try {
            ExcludedDomain::destroy($id);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
