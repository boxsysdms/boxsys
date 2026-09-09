<?php

declare(strict_types=1);

namespace App\Data\Permissions;

use App\Enums\PermissionScope;
use Spatie\LaravelData\Dto;

final class ListPermissionsData extends Dto
{
    public function __construct(
        public readonly PermissionScope|string $scope,
        public readonly ?int $page = null,
        public readonly ?int $perPage = null
    ) {}
}
