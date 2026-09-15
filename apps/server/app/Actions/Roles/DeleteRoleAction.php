<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Models\Role;

/**
 * Delete the specified role.
 */
final class DeleteRoleAction
{
    /**
     * Handle role deletion.
     */
    public function handle(Role $role): bool
    {
        return (bool) $role->delete();
    }
}
