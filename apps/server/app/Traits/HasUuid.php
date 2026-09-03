<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasUuid
{
    /**
     * Find a model by its UUID.
     */
    public static function findByUuid(string $uuid): ?self
    {
        return static::where('uuid', $uuid)->first();
    }

    /**
     * Find a model by its UUID or fail.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByUuidOrFail(string $uuid): self
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Boot the trait and assign a UUID to the model upon creation.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (self $model) {
            $model->attributes['uuid'] = $model->uuid ?: Str::uuid()->toString();
        });
    }
}
