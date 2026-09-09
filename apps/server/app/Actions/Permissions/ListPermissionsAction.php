<?php

declare(strict_types=1);

namespace App\Actions\Permissions;

use App\Data\Permissions\ListPermissionsData;
use App\Models\Permission;

final class ListPermissionsAction
{
    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Permission>
     */
    public function handle(ListPermissionsData $data)
    {
        return Permission::query()
            ->where('scope', $data->scope)
            ->orderBy('name', 'asc')
            ->paginate(page: $data->page, perPage: $data->perPage);
    }
}
