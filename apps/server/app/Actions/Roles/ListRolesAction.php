<?php

declare(strict_types=1);

namespace App\Actions\Roles;

use App\Data\Roles\ListRolesData;
use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

/**
 * List roles based on the provided criteria.
 *
 * Roles can be filtered by permission scope and searched by display name
 * or description using the application's current locale. Results can be
 * sorted by supported fields and are returned using pagination.
 */
final class ListRolesAction
{
    /**
     * Handle roles listing.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Role>
     */
    public function handle(ListRolesData $data)
    {
        $locale = app()->getLocale();

        return $this
            ->baseQuery($data->scope)
            ->when($data->search, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike("display_name->{$locale}", "%{$search}%")
                    ->orwhereLike("description->{$locale}", "%{$search}%")
            ))
            ->orderBy(
                $this->resolveSortColumn($data->sortBy, $locale),
                $data->sortOrder
            )
            ->paginate(page: $data->page, perPage: $data->perPage);
    }

    /**
     * Get the base query for the given permission scope.
     *
     * @return Builder<Role>
     */
    private function baseQuery(PermissionScope $scope): Builder
    {
        return $scope === PermissionScope::SYSTEM
            ? Role::query()->system()
            : Role::query()->collection();
    }

    /**
     * Resolve the column name for sorting based on the provided sort key
     * and locale.
     */
    private function resolveSortColumn(string $sortBy, string $locale): string
    {
        return in_array($sortBy, ['display_name', 'description'], true)
            ? "{$sortBy}->{$locale}"
            : $sortBy;
    }
}
