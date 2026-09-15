<?php

declare(strict_types=1);

namespace App\Http\Resources\Roles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Role
 */
final class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'scope' => $this->scope->value,
            'displayName' => $this->display_name,
            'description' => $this->description,
            'usersCount' => $this->whenCounted('users') ?? 0,
            'permissionsCount' => $this->whenCounted('permissions') ?? 0,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
        ];
    }
}
