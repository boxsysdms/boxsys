<?php

declare(strict_types=1);

namespace App\Http\Resources\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/**
 * @mixin \App\Models\Permission
 */
final class PermissionResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     *
     * @var array<string>
     */
    public $attributes = [
        'description',
    ];

    /**
     * Get the resource's ID.
     */
    public function toId(Request $request): string
    {
        return $this->name;
    }
}
