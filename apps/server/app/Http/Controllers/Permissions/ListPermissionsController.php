<?php

declare(strict_types=1);

namespace App\Http\Controllers\Permissions;

use App\Actions\Permissions\ListPermissionsAction;
use App\Data\Permissions\ListPermissionsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\ListPermissionsRequest;
use App\Http\Resources\Permissions\PermissionResource;

final class ListPermissionsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<PermissionResource>
     */
    public function __invoke(ListPermissionsRequest $request, ListPermissionsAction $action)
    {
        $permissions = $action->handle(
            ListPermissionsData::from($request->validated())
        );

        return PermissionResource::collection($permissions);
    }
}
