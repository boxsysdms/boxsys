<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermissionScope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read PermissionScope $scope
 * @property-read string|null $description
 * @property-read string $guard_name
 */
#[Unguarded]
#[Translatable('description')]
final class Permission extends SpatiePermission
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory, HasTranslations;

    public $timestamps = false;

    /**
     * Scope a query to only include system permissions.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('scope', PermissionScope::SYSTEM);
    }

    /**
     * Scope a query to only include collection permissions.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCollection(Builder $query): Builder
    {
        return $query->where('scope', PermissionScope::COLLECTION);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scope' => PermissionScope::class,
        ];
    }
}
