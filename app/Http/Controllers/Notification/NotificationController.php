<?php

namespace App\Http\Controllers\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotificationController extends Controller
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

            $user->unreadNotifications->markAsRead();

            $notifications = $user->notifications()->paginate($perPage);

            return NotificationResource::collection($notifications);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $notification)
    {
        try {
            DB::table('notifications')
                ->where('id', $notification)
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
}
