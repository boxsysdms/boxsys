<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Role::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sortByOptions = [
            'display_name',
            'nameDisplay',
            'description',
            'created_at',
            'createdAt',
            'updated_at',
            'updatedAt',
        ];

        return [
            'scope' => ['required', 'string', Rule::enum(PermissionScope::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'sortBy' => [
                'nullable',
                'string',
                'in:'.implode(',', $sortByOptions),
            ],
            'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:15', 'max:50'],
        ];
    }
}
