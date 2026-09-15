<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $displayNameEn = fake()->unique()->words(2, true);

        return [
            'name' => str($displayNameEn)->lower()->replace(' ', '.')->value(),
            'display_name' => [
                'en' => $displayNameEn,
                'pt' => fake()->words(2, true),
            ],
            'scope' => fake()->randomElement(array_column(PermissionScope::cases(), 'value')),
            'description' => [
                'en' => fake()->sentence(),
                'pt' => fake()->sentence(),
            ],
            'guard_name' => 'web',
        ];
    }

    /**
     * Set the role's scope to SYSTEM.
     */
    public function system(): self
    {
        return $this->state(fn () => [
            'scope' => PermissionScope::SYSTEM,
        ]);
    }

    /**
     * Set the role's scope to COLLECTION.
     */
    public function collection(): self
    {
        return $this->state(fn () => [
            'scope' => PermissionScope::COLLECTION,
        ]);
    }
}
