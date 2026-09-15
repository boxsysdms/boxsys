<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;

final class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ROLES_LIST->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(Permission::ROLES_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ROLES_CREATE->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can(Permission::ROLES_UPDATE->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can(Permission::ROLES_DELETE->value);
    }

    public function manageUsers(User $user, Role $role): bool
    {
        return $user->can(Permission::ROLES_USERS->value);
    }

    public function managePermissions(User $user, Role $role): bool
    {
        return $user->can(Permission::ROLES_PERMISSIONS->value);
    }
}
