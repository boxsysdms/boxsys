<?php

declare(strict_types=1);

use App\Enums\PermissionScope;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'models', 'permissions');

it('has correct array keys and types', function () {
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

it('casts scope to enum', function () {
    $permission = Permission::factory()->create()->refresh();

    expect($permission->scope)->toBeInstanceOf(PermissionScope::class);
});

it('has translations for description', function () {
    $permission = Permission::factory()->create()->refresh();

    $translations = $permission->getTranslations('description');

    expect($translations)->toHaveKeys(['en', 'pt']);
});

it('filters permissions by scope using query scopes', function () {
    Permission::factory()->system()->create();
    Permission::factory()->collection()->create();

    expect(Permission::system()->count())->toBe(1);
    expect(Permission::collection()->count())->toBe(1);
});
