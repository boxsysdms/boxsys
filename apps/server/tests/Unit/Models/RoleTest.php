<?php

declare(strict_types=1);

use App\Enums\PermissionScope;
use App\Models\Role;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Tests\Helpers\testSoftDeletes;
use function Tests\Helpers\testUuidField;

uses(RefreshDatabase::class)->group('unit', 'models', 'roles');

describe('attributes', function () {
    it('has the expected attributes', function () {
        $role = Role::factory()->system()->create()->refresh();

        expect($role->toArray())
            ->toHaveKeys([
                'id',
                'uuid',
                'name',
                'display_name',
                'scope',
                'description',
                'guard_name',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    });
});

describe('casts', function () {
    it('casts attributes to the expected types', function () {
        $role = Role::factory()->system()->create()->refresh();

        expect($role)
            ->id->toBeInt()
            ->uuid->toBeUuid()
            ->name->toBeString()
            ->display_name->toBeString()
            ->scope->toBeInstanceOf(PermissionScope::class)
            ->description->toBeString()
            ->created_at->toBeInstanceOf(CarbonImmutable::class)
            ->updated_at->toBeInstanceOf(CarbonImmutable::class)
            ->deleted_at->toBeNull();
    });
});

describe('translations', function () {
    it('supports translations for display_name', function () {
        $role = Role::factory()->create()->refresh();

        expect($role->getTranslations('display_name'))->toHaveKeys(['en', 'pt']);
    });

    it('supports translations for description', function () {
        $role = Role::factory()->create()->refresh();

        expect($role->getTranslations('description'))->toHaveKeys(['en', 'pt']);
    });
});

describe('query scopes', function () {
    it('filters roles by system scope', function () {
        Role::factory()->system()->create();
        Role::factory()->collection()->create();

        expect(Role::system()->count())->toBe(1);
    });

    it('filters roles by collection scope', function () {
        Role::factory()->system()->create();
        Role::factory()->collection()->create();

        expect(Role::collection()->count())->toBe(1);
    });
});

testUuidField(Role::class);

testSoftDeletes(Role::class);
