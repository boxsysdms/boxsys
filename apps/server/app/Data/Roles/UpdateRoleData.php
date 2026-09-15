<?php

declare(strict_types=1);

namespace App\Data\Roles;

use Spatie\LaravelData\Dto;

final class UpdateRoleData extends Dto
{
    /**
     * @param  string|array<string, string>|null  $displayName
     * @param  string|array<string, string>|null  $description
     */
    public function __construct(
        public readonly string|array|null $displayName = null,
        public readonly string|array|null $description = null
    ) {}
}
