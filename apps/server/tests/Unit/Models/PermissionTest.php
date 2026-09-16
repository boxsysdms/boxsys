<?php

declare(strict_types=1);

use App\Enums\PermissionScope;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'models', 'permissions');

describe('attributes', function () {
    it('has the expected attributes and types', function () {
        $permission = Permission::factory()->create()->refresh();

        expect($permission->toArray())
            ->toHaveKeys([
                'id',
                'name',
                'scope',
                'description',
            ]);

        expect($permission)
            ->id->toBeInt()
            ->name->toBeString()
            ->scope->not->toBeNull()
            ->description->toBeString()
            ->guard_name->toBe('web');
    });

    it('casts scope to PermissionScope', function () {
        $permission = Permission::factory()->create()->refresh();

        expect($permission->scope)->toBeInstanceOf(PermissionScope::class);
    });
});

describe('translations', function () {
    it('has translations for description', function () {
        $permission = Permission::factory()->create()->refresh();

        $translations = $permission->getTranslations('description');

        expect($translations)->toHaveKeys(['en', 'pt']);
    });
});

describe('query scopes', function () {
    it('filters permissions by scope', function () {
        Permission::factory()->system()->create();
        Permission::factory()->collection()->create();

        expect(Permission::system()->count())->toBe(1);
        expect(Permission::collection()->count())->toBe(1);
    });
});
