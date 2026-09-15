<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Enums\PermissionScope;
use App\Models\Role;
use App\Rules\Translatable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scope' => ['required', 'string', Rule::enum(PermissionScope::class)],
            'displayName' => [
                'required',
                new Translatable(minLength: 3, maxLength: 100, minItems: 1, maxItems: 25),
            ],
            'description' => [
                'nullable',
                new Translatable(minLength: 3, maxLength: 100, maxItems: 25),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'displayName' => 'displayName',
        ];
    }
}
