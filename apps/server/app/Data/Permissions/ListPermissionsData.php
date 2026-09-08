<?php

declare(strict_types=1);

namespace App\Data\Permissions;

use App\Enums\PermissionScope;
use Illuminate\Support\Str;
use Spatie\LaravelData\Dto;

final class ListPermissionsData extends Dto
{
    public readonly string $sortBy;

    public readonly string $sortOrder;

    public function __construct(
        public readonly PermissionScope|string $scope,
        public readonly ?int $page = null,
        public readonly ?int $perPage = null,
        ?string $sortBy = 'name',
        ?string $sortOrder = 'asc',
    ) {
        $this->sortBy = Str::snake($sortBy ?? 'name');

        $this->sortOrder = $sortOrder ?? 'asc';
    }
}
