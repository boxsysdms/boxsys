<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermissionScope;
use App\Traits\HasUuid;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read int $id
 * @property-read string $uuid
 * @property-read string $name
 * @property-read PermissionScope $scope
 * @property-read string $display_name
 * @property-read string|null $description
 * @property-read string $guard_name
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Support\Carbon|null $deleted_at
 */
#[Unguarded]
#[Translatable('display_name', 'description')]
final class Role extends SpatieRole
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, HasTranslations, HasUuid, SoftDeletes;

    /**
     * The relationships that should always be counted.
     *
     * @var list<string>
     */
    protected $withCount = [
        'permissions',
        'users',
    ];

    /**
     * Scope a query to only include system roles.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('scope', PermissionScope::SYSTEM);
    }

    /**
     * Scope a query to only include collection roles.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCollection(Builder $query): Builder
    {
        return $query->where('scope', PermissionScope::COLLECTION);
    }

    /**
     * Get all of the users that are assigned this role.
     *
     * @override
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, $this> */
        return $this->morphedByMany(
            User::class,
            'model',
            'model_has_roles',
            'role_id',
            'model_id'
        );
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
