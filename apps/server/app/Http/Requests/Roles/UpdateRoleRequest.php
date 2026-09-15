<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Rules\Translatable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->role) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'displayName' => [
                'sometimes',
                'required',
                new Translatable(minLength: 3, maxLength: 100, maxItems: 25),
            ],
            'description' => [
                'sometimes',
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
