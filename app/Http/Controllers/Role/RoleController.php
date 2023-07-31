<?php

namespace App\Http\Controllers\Role;

use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\RolesConstant;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Constants\PermissionConstant;
use App\Http\Requests\Role\CopyRequest;
use App\Http\Resources\Role\RoleResource;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\UpdatePermissionRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            hasPermissionTo(PermissionConstant::ROLES_ACCESS['name']);

            $perPage = $request->query('perPage', config('app.per_page'));
            $sortByKey = $request->query('sortByKey', 'id');
            $sortByOrder = $request->query('sortByOrder', 'desc');
            $searchQuery = $request->query('searchQuery');
            $name = @$searchQuery['name'];

            $query  = Role::query()->withCount(['users']);

            $query->when(
                $name,
                function ($query, $name) {
                    $query->where('name', 'ILIKE', "%$name%");
                }
            );

            $query->orderBy($sortByKey, $sortByOrder);

            $data = $query->paginate($perPage);

            return RoleResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            hasPermissionTo(PermissionConstant::ROLES_CREATE['name']);

            $validated = $request->validated();

            Role::create([
                ...$validated,
                'guard_name' => 'api',
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
    public function show(string $role)
    {
        try {
            $data = Role::with(['permissions'])->findOrFail($role);

            return new RoleResource($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $role)
    {
        try {
            hasPermissionTo(PermissionConstant::ROLES_EDIT['name']);

            $validated = $request->validated();

            $roleModel = Role::findOrFail($role);

            $roleModel->update($validated);

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
    public function destroy(string $role)
    {
        try {
            hasPermissionTo(PermissionConstant::ROLES_DELETE['name']);

            $roleModel = Role::findOrFail($role);

            if ($roleModel->name == RolesConstant::ADMIN) {
                abort(422, 'Admin Role Cannot Be Deleted');
            }

            $users_count = User::role($roleModel->name)->count();

            if ($users_count > 0) {
                abort(422, 'Role Has User');
            }

            $roleModel->delete();

            return response()->noContent();
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function all()
    {
        try {
            $data = Role::all();

            return RoleResource::collection($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function updatePermissions(UpdatePermissionRequest $request, string $role)
    {
        try {
            hasPermissionTo(PermissionConstant::PERMISSIONS_EDIT['name']);

            $validated = $request->validated();

            $roleModel =  Role::findOrFail($role);

            if (to_boolean($validated['is_all_checked'])) {
                if (to_boolean($validated['is_attach'])) {
                    foreach ($validated['permission_ids'] as $permission_id) {
                        $roleModel->givePermissionTo($permission_id);
                    }
                } else {
                    foreach ($validated['permission_ids'] as $permission_id) {
                        $roleModel->revokePermissionTo($permission_id);
                    }
                }
            } else {
                if (to_boolean($validated['is_attach'])) {
                    $roleModel->givePermissionTo($validated['permission_id']);
                } else {
                    $roleModel->revokePermissionTo($validated['permission_id']);
                }
            }

            $data =  $roleModel->fresh('permissions');

            return new RoleResource($data);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function copy(CopyRequest $request)
    {
        try {
            $validated = $request->validated();

            $role = Role::with(['permissions'])->findOrFail($validated['role_id']);
            $permissions = @$role->permissions;

            $model = DB::transaction(function () use ($validated, $permissions) {
                $model = Role::create([
                    'name' => $validated['name'],
                ]);

                $model->syncPermissions($permissions);

                return $model;
            });

            return new RoleResource($model);
        } catch (HttpException $th) {
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
