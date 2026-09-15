<?php

declare(strict_types=1);

namespace App\Http\Controllers\Roles;

use App\Actions\Roles\CreateRoleAction;
use App\Actions\Roles\DeleteRoleAction;
use App\Actions\Roles\ListRolesAction;
use App\Actions\Roles\UpdateRoleAction;
use App\Data\Roles\CreateRoleData;
use App\Data\Roles\ListRolesData;
use App\Data\Roles\UpdateRoleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\CreateRoleRequest;
use App\Http\Requests\Roles\ListRolesRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\Roles\RoleResource;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

final class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<RoleResource>
     */
    public function index(ListRolesRequest $request, ListRolesAction $action)
    {
        $roles = $action->handle(
            ListRolesData::from($request->validated())
        );

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RoleResource
     */
    public function store(CreateRoleRequest $request, CreateRoleAction $action)
    {
        $role = $action->handle(
            CreateRoleData::from($request->validated())
        );

        // Automatically loaded counts are not applied to newly created models
        $role->loadCount(['users', 'permissions']);

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): RoleResource
    {
        Gate::authorize('view', $role);

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role, UpdateRoleAction $action): RoleResource
    {
        $role = $action->handle(
            $role,
            UpdateRoleData::from($request->validated())
        );

        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role, DeleteRoleAction $action)
    {
        Gate::authorize('delete', $role);

        $action->handle($role);

        return response()->noContent();
    }
}
