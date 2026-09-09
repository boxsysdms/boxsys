<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PermissionScope;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
final class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'scope' => fake()->randomElement(
                array_column(PermissionScope::cases(), 'value')
            ),
            'description' => [
                'en' => fake()->sentence(),
                'pt' => fake()->sentence(),
            ],
            'guard_name' => 'web',
        ];
    }

    /**
     * Set the permission scope to SYSTEM.
     */
    public function system(): self
    {
        return $this->state(fn(array $attributes) => [
            'scope' => PermissionScope::SYSTEM,
        ]);
    }

    /**
     * Set the permission scope to COLLECTION.
     */
    public function collection(): self
    {
        return $this->state(fn(array $attributes) => [
            'scope' => PermissionScope::COLLECTION,
        ]);
    }
}
