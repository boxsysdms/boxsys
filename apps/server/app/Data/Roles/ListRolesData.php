<?php

declare(strict_types=1);

namespace App\Data\Roles;

use App\Enums\PermissionScope;
use Illuminate\Support\Str;
use Spatie\LaravelData\Dto;

final class ListRolesData extends Dto
{
    public readonly string $sortBy;

    public function __construct(
        public readonly PermissionScope|string $scope,
        public readonly ?string $search = null,
        ?string $sortBy = 'display_name',
        public readonly string $sortOrder = 'asc',
        public readonly ?int $page = null,
        public readonly ?int $perPage = null
    ) {
        $this->sortBy = Str::snake($sortBy);
    }
}
