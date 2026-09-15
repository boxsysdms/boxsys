<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Data\Roles\CreateRoleData;
use App\Models\Role;

/**
 * Create a new role.
 *
 * The role name is derived from the display name. If the display name is
 * provided as a string, it is used as-is. If it is provided as a localized
 * array, the value for the application's fallback locale is used when
 * available; otherwise, the first available translation is used.
 */
final class CreateRoleAction
{
    /**
     * Handle role creation.
     */
    public function handle(CreateRoleData $data): Role
    {
        /** @var Role */
        return Role::create([
            'name' => $this->resolveNameForCreate($data->displayName),
            'scope' => $data->scope,
            'display_name' => $data->displayName,
            'description' => $data->description,
            'guard_name' => 'web',
        ]);
    }

    /**
     * Resolve the name for creating the role based on the display name and the
     * application's fallback locale.
     *
     * @param  string|array<string, string>  $displayName
     */
    private function resolveNameForCreate(string|array $displayName): string
    {
        $fallbackLocale = app()->getFallbackLocale();

        return is_string($displayName)
            ? $displayName
            : ($displayName[$fallbackLocale] ?? (string) reset($displayName));
    }
}
