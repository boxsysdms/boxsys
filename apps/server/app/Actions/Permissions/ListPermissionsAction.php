<?php

declare(strict_types=1);

namespace App\Actions\Permissions;

use App\Data\Permissions\ListPermissionsData;
use App\Enums\PermissionScope;
use App\Models\Permission;

/**
 * Handles the retrieval of permissions with filtering and pagination.
 */
final class ListPermissionsAction
{
    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Permission>
     */
    public function handle(ListPermissionsData $data)
    {
        $query = $data->scope === PermissionScope::SYSTEM
            ? Permission::query()->system()
            : Permission::query()->collection();

        return $query
            ->orderBy('name')
            ->paginate(page: $data->page, perPage: $data->perPage);
    }
}
