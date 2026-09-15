<?php

declare(strict_types=1);

namespace App\Data\Permissions;

use App\Enums\Permission;
use Spatie\LaravelData\Dto;

final class SyncPermissionsData extends Dto
{
    /**
     * @param  list<Permission|int|string>  $permissions
     */
    public function __construct(
        public readonly array $permissions
    ) {}
}
