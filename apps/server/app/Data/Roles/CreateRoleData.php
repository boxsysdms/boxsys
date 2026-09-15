<?php

declare(strict_types=1);

namespace App\Data\Roles;

use App\Enums\PermissionScope;
use Spatie\LaravelData\Dto;

final class CreateRoleData extends Dto
{
    /**
     * @param  string|array<string, string>  $displayName
     * @param  string|array<string, string>|null  $description
     */
    public function __construct(
        public readonly PermissionScope|string $scope,
        public readonly string|array $displayName,
        public readonly string|array|null $description = null
    ) {}
}
