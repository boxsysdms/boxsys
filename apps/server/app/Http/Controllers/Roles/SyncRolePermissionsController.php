<?php

declare(strict_types=1);

namespace App\Http\Controllers\Roles;

use App\Actions\Roles\SyncRolePermissionsAction;
use App\Data\Permissions\SyncPermissionsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\SyncRolePermissionsRequest;
use App\Models\Role;

final class SyncRolePermissionsController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SyncRolePermissionsRequest $request, Role $role, SyncRolePermissionsAction $action)
    {
        $action->handle(
            $role,
            new SyncPermissionsData(
                permissions: $request->validated('permissions')
            )
        );

        return response()->noContent();
    }
}
