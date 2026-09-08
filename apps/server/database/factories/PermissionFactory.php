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
            'scope' => fake()->randomElement([PermissionScope::COLLECTION, PermissionScope::SYSTEM]),
            'description' => [
                'en' => fake('en')->sentence(),
                'pt' => fake('pt_PT')->sentence(),
            ],
            'guard_name' => 'web',
        ];
    }
}
