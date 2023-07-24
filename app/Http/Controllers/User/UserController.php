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

            $query  = User::query()->with(['roles']);

            $query->when($name, function ($query, $name) {
                $query->where('name', 'ILIKE', "%$name%");
            });

            $query->orderBy($sortByKey, $sortByOrder);

            $data = $query->paginate($perPage);

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

            $role = Role::findOrFail($validated['role_id']);

            DB::transaction(function () use ($validated, $role) {
                $user = User::create($validated);
                $user->assignRole($role);
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
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_EDIT['name']);

            $validated = $request->validated();

            $role = Role::findOrFail($validated['role_id']);
            $user = User::findOrFail($id);

            DB::transaction(function () use ($validated, $user, $role) {
                $user->update($validated);
                $user->syncRoles($role);
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
    public function destroy(string $id)
    {
        try {
            hasPermissionTo(PermissionConstant::USERS_DELETE['name']);

            User::destroy($id);

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
            $user = auth()->user();

            $user->update($validated);

            $data = $user->fresh(['roles.permissions']);

            return new UserResource($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
