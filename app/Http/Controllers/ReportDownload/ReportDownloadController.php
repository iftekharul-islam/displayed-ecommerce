<?php

namespace App\Http\Controllers\ReportDownload;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportDownload\ReportDownloadResource;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportDownloadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $request_all = $request->all();
            $perPage = data_get($request_all, 'perPage', config('app.per_page'));
            $user = auth()->user();

            $notifications = $user->notifications()
                ->paginate($perPage);

            return ReportDownloadResource::collection($notifications);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $report_download)
    {
        try {
            DB::table('notifications')
                ->where('id', $report_download)
                ->delete();

            return response()->noContent();
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }


    public function unreadCount()
    {
        try {
            $user = auth()->user();
            $count = $user->unreadNotifications()->count();

            return response()->json(['count' => $count]);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function markAsRead(string $report_download)
    {
        try {
            DB::table('notifications')
                ->where('id', $report_download)
                ->update([
                    'read_at' => now(),
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
}
