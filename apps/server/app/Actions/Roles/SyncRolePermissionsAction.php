<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Data\Permissions\SyncPermissionsData;
use App\Models\Role;

/**
 * Synchronize the permissions assigned to a role.
 *
 * Permissions can be provided as IDs, names, or Permission enums. Permissions
 * not included in the provided set are removed, while permissions included in
 * the set are added or retained as necessary. When an empty array is provided,
 * all permissions assigned to the role are removed.
 */
final class SyncRolePermissionsAction
{
    /**
     * Handle permission synchronization for a role.
     */
    public function handle(Role $role, SyncPermissionsData $data): void
    {
        $role->syncPermissions($data->permissions);
    }
}
