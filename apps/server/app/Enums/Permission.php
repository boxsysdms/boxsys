<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    // Roles
    case ROLES_CREATE = 'system.roles.create';
    case ROLES_DELETE = 'system.roles.delete';
    case ROLES_LIST = 'system.roles.list';
    case ROLES_PERMISSIONS = 'system.roles.permissions';
    case ROLES_UPDATE = 'system.roles.update';
    case ROLES_USERS = 'system.roles.users';
    case ROLES_VIEW = 'system.roles.view';

    // Test
    case TEST_SCOPE = 'collection.test.scope';

    public function scope(): string
    {
        if (str()->startsWith($this->value, 'system.')) {
            return PermissionScope::SYSTEM->value;
        }

        return PermissionScope::COLLECTION->value;
    }
}
