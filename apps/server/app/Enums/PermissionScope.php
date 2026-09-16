<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Defines the available scopes for permissions and roles.
 */
enum PermissionScope: string
{
    case COLLECTION = 'collection';
    case SYSTEM = 'system';
}
