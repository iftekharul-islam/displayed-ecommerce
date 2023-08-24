<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserProfileRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $name = @$searchQuery['name'];

            $data  = User::query()->with(['roles'])
                ->when($name, function ($query, $name) {
                    $query->where('name', 'LIKE', "%$name%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return UserResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_CREATE['name']);

            $validated = $request->validated();

            $roleModel = Role::findOrFail($validated['role_id']);

            DB::transaction(function () use ($validated, $roleModel) {
                $userModel = User::create($validated);
                $userModel->assignRole($roleModel);
            });

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
    public function update(UpdateUserRequest $request, string $user)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_EDIT['name']);

            $validated = $request->validated();

            $roleModel = Role::findOrFail($validated['role_id']);
            $userModel = User::findOrFail($user);

            DB::transaction(function () use ($validated, $userModel, $roleModel) {
                $userModel->update($validated);
                $userModel->syncRoles($roleModel);
            });

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
    public function destroy(string $user)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_DELETE['name']);

            User::destroy($user);

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function updateProfile(UpdateUserProfileRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_PROFILE_EDIT['name']);

            $validated = $request->validated();
            $authUser = auth()->user();

            $authUser->update($validated);

            $data = $authUser->fresh(['roles.permissions']);

            return new UserResource($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }


    public function trashes(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_SOFT_DELETE_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $name = @$searchQuery['name'];

            $data  = User::query()
                ->onlyTrashed()
                ->with(['roles'])
                ->when($name, function ($query, $name) {
                    $query->where('name', 'LIKE', "%$name%");
                })
                ->orderBy($sortByKey, $sortByOrder)
                ->paginate($perPage);

            return UserResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function restore($user)
    {
        try {
            User::query()->onlyTrashed()->findOrFail($user)->restore();

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
            User::query()->onlyTrashed()->forceDelete();

            return response()->json([
                'message' => 'Successfully deleted',
            ], 200);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
