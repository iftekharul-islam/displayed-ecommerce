<?php

namespace App\Http\Controllers\ExcludedDomain;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\ExcludedDomain;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\ExcludedDomain\StoreExcludedDomainRequest;

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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
