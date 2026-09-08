<?php

declare(strict_types=1);

use App\Actions\Permissions\ListPermissionsAction;
use App\Data\Permissions\ListPermissionsData;
use App\Enums\PermissionScope;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'permissions');

describe('listing permissions', function () {
    it('returns a list of permissions', function () {
        Permission::factory()
            ->count(5)
            ->sequence(
                ['scope' => PermissionScope::COLLECTION],
                ['scope' => PermissionScope::SYSTEM],
                ['scope' => PermissionScope::COLLECTION],
                ['scope' => PermissionScope::SYSTEM],
                ['scope' => PermissionScope::SYSTEM],
            )
            ->create();

        $data = new ListPermissionsData(PermissionScope::COLLECTION);

        $result = resolve(ListPermissionsAction::class)->handle($data);

        expect($result)->toHaveCount(2);
    });

    it('returns an empty list when no permissions exist', function () {
        $data = new ListPermissionsData(PermissionScope::SYSTEM);

        $result = resolve(ListPermissionsAction::class)->handle($data);

        expect($result)->toHaveCount(0);
    });
});

describe('sorting permissions', function () {
    it('returns a list of permissions sorted by name in ascending order by default', function () {
        Permission::factory()
            ->count(3)
            ->sequence(
                ['name' => 'Mango', 'scope' => PermissionScope::SYSTEM],
                ['name' => 'Zebra', 'scope' => PermissionScope::SYSTEM],
                ['name' => 'Apple', 'scope' => PermissionScope::SYSTEM]
            )
            ->create();

        $data = new ListPermissionsData(PermissionScope::SYSTEM);

        $result = resolve(ListPermissionsAction::class)->handle($data);

        expect($result[0]->name)->toBe('Apple');
        expect($result[1]->name)->toBe('Mango');
        expect($result[2]->name)->toBe('Zebra');
    });

    it('returns a list of permissions sorted by name in descending order when specified', function () {
        Permission::factory()->createMany([
            ['name' => 'Zebra', 'scope' => PermissionScope::SYSTEM],
            ['name' => 'Apple', 'scope' => PermissionScope::SYSTEM],
            ['name' => 'Mango', 'scope' => PermissionScope::SYSTEM],
        ]);

        $data = new ListPermissionsData(
            scope: PermissionScope::SYSTEM,
            sortBy: 'name',
            sortOrder: 'desc'
        );

        $result = resolve(ListPermissionsAction::class)->handle($data);

        expect($result[0]->name)->toBe('Zebra');
        expect($result[1]->name)->toBe('Mango');
        expect($result[2]->name)->toBe('Apple');
    });
});
