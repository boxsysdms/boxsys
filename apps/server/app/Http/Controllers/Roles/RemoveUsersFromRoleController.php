<?php

declare(strict_types=1);

namespace App\Http\Controllers\Roles;

use App\Actions\Roles\RemoveUserFromRoleAction;
use App\Data\Users\UserIdentifiersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\ManageRoleUsersRequest;
use App\Models\Role;

final class RemoveUsersFromRoleController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke(ManageRoleUsersRequest $request, Role $role, RemoveUserFromRoleAction $action)
    {
        $action->handle(
            $role,
            UserIdentifiersData::from($request->validated())
        );

        return response()->noContent();
    }
}
