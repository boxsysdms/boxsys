<?php

declare(strict_types=1);

use App\Actions\Roles\SyncRolePermissionsAction;
use App\Data\Permissions\SyncPermissionsData;
use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'permissions', 'roles');

beforeEach(function () {
    test()->role = Role::factory()->system()->create();

    test()->permissions = Permission::factory(3)
        ->system()
        ->sequence(
            ['name' => PermissionEnum::ROLES_CREATE],
            ['name' => PermissionEnum::ROLES_VIEW],
            ['name' => PermissionEnum::ROLES_LIST],
        )
        ->create();

    test()->role->givePermissionTo(
        PermissionEnum::ROLES_LIST->value,
        PermissionEnum::ROLES_CREATE->value
    );
});

describe('sync', function () {

    it('syncs permissions using string names', function () {
        $data = new SyncPermissionsData(permissions: [
            PermissionEnum::ROLES_LIST->value,
            PermissionEnum::ROLES_VIEW->value,
        ]);

        resolve(SyncRolePermissionsAction::class)->handle(test()->role, $data);

        $names = test()->role->fresh()->permissions->pluck('name')->sort()->values()->all();

        expect($names)->toBe([PermissionEnum::ROLES_LIST->value, PermissionEnum::ROLES_VIEW->value]);
        expect(test()->role->permissions->count())->toBe(2);
    });

    it('syncs permissions using enums', function () {
        $data = new SyncPermissionsData(permissions: [
            PermissionEnum::ROLES_LIST,
            PermissionEnum::ROLES_VIEW,
        ]);

        resolve(SyncRolePermissionsAction::class)->handle(test()->role, $data);

        $names = test()->role->fresh()->permissions->pluck('name')->sort()->values()->all();

        expect($names)->toBe([PermissionEnum::ROLES_LIST->value, PermissionEnum::ROLES_VIEW->value]);
        expect(test()->role->permissions->count())->toBe(2);
    });

    it('syncs permissions using models', function () {
        $data = new SyncPermissionsData(permissions: [
            test()->permissions[1],
            test()->permissions[2],
        ]);

        resolve(SyncRolePermissionsAction::class)->handle(test()->role, $data);

        $names = test()->role->fresh()->permissions->pluck('name')->sort()->values()->all();

        expect($names)->toBe([PermissionEnum::ROLES_LIST->value, PermissionEnum::ROLES_VIEW->value]);
        expect(test()->role->permissions->count())->toBe(2);
    });

    it('removes all permissions when given an empty array', function () {
        $data = new SyncPermissionsData(permissions: []);

        resolve(SyncRolePermissionsAction::class)->handle(test()->role, $data);

        $names = test()->role->fresh()->permissions->pluck('name')->sort()->values()->all();

        expect($names)->toBe([]);
        expect(test()->role->permissions->count())->toBe(0);
    });
});
