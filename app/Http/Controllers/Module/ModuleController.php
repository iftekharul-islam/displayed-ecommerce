<?php

namespace App\Http\Controllers\Module;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Module\ModuleResource;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ModuleController extends Controller
{
    public function __invoke(Request $request)
    {
        $perPage = $request->query('perPage', config('app.per_page'));
        $sortByKey = $request->query('sortByKey', 'id');
        $sortByOrder = $request->query('sortByOrder', 'desc');

        try {
            $data = Module::query()->with(['permissions'])
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return ModuleResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
