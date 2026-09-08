<?php

declare(strict_types=1);

use App\Models\Permission;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'models', 'permissions');

describe('model structure', function () {
    it('has correct array keys and types', function () {
        $permission = Permission::factory()->create()->refresh();

        expect($permission->toArray())
            ->toHaveKeys([
                'id',
                'name',
                'scope',
                'description',
                'created_at',
                'updated_at',
            ]);

        expect($permission)
            ->id->toBeInt()
            ->name->toBeString()
            ->scope->not->toBeNull()
            ->description->toBeString()
            ->guard_name->toBe('web')
            ->created_at->toBeInstanceOf(CarbonImmutable::class)
            ->updated_at->toBeInstanceOf(CarbonImmutable::class);
    });

    it('casts scope to enum', function () {
        $permission = Permission::factory()->create()->refresh();

        expect($permission->scope)->toBeInstanceOf(App\Enums\PermissionScope::class);
    });
});

describe('model translations', function () {
    it('has correct translations for description', function () {
        $permission = Permission::factory()->create()->refresh();

        $translations = $permission->getTranslations('description');

        expect($translations)
            ->toHaveKey('en')
            ->toHaveKey('pt')
            ->en->toBeString()
            ->pt->toBeString();
    });
});
