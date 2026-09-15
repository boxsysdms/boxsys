<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Rules\UuidOrEmail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ManageRoleUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageUsers', $this->role) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users' => ['required', 'array', 'min:1', 'max:50'],
            'users.*' => [
                'required',
                new UuidOrEmail(),
                'max:50',
            ],
        ];
    }
}
