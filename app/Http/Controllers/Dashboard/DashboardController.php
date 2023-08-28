<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Campaign;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DashboardController extends Controller
{
    public  function campaignsSummaries()
    {
        try {
            $campaigns = Campaign::select(['id', 'name'])
                ->withCount([
                    'excludedDomains as total_excluded_domain',
                    'shortUrls as total_included_domain',
                    'shortUrls as active_domain' => fn ($query) => $query->where('expired_at', '>=', now()->format('Y-m-d')),
                    'shortUrls as expired_domain' => fn ($query) => $query->where('expired_at', '<', now()->format('Y-m-d')),
                ])
                ->orderBy('id', 'asc')
                ->get();

            return response()->json($campaigns);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
