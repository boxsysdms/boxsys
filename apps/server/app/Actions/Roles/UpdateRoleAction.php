<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Data\Roles\UpdateRoleData;
use App\Models\Role;

/**
 * Update the specified role with the provided data.
 *
 * Only provided fields are updated. The role name is derived from the display
 * name using the application's fallback locale. Translatable values are
 * restricted to the application's supported locales.
 */
final class UpdateRoleAction
{
    /**
     * Update the given role with the provided data.
     */
    public function handle(Role $role, UpdateRoleData $data): Role
    {
        $payload = [
            'name' => $this->resolveNameForUpdate($data->displayName),
            'display_name' => $this->normalizeTranslatable($data->displayName),
            'description' => $this->normalizeTranslatable($data->description),
        ];

        $payload = array_filter(
            $payload,
            fn ($value) => ! is_null($value) && $value !== '' && $value !== []
        );

        if ($payload !== []) {
            $role->update($payload);
        }

        return $role->refresh();
    }

    /**
     * Resolve the name for updating the role based on the display name and the
     * application's fallback locale.
     *
     * @param  string|array<string, string>  $displayName
     */
    private function resolveNameForUpdate(string|array|null $displayName): ?string
    {
        if (is_null($displayName)) {
            return null;
        }

        $fallbackLocale = app()->getFallbackLocale();

        return is_string($displayName) && $fallbackLocale === app()->getLocale()
            ? $displayName
            : ($displayName[$fallbackLocale] ?? null);
    }

    /**
     * Filter the given array of translatable values to only include
     * supported locales.
     *
     * @param  array<string, string>|null  $values
     * @return array<string, string>
     */
    private function filterLocales(?array $values): array
    {
        $supportedLocales = config('boxsys.locales.supported', []);

        return array_intersect_key($values ?? [], array_flip($supportedLocales));
    }

    /**
     * Normalize the given translatable value by filtering its locales.
     *
     * @param  string|array<string, string>|null  $value
     * @return string|array<string, string>|null
     */
    private function normalizeTranslatable(string|array|null $value): string|array|null
    {
        return is_string($value) || is_null($value)
            ? $value
            : $this->filterLocales($value);
    }
}
