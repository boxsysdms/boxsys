<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionScope: string
{
    case COLLECTION = 'collection';
    case SYSTEM = 'system';
}
