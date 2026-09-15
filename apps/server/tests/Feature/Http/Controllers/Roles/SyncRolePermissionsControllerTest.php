<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Tests\Helpers\testAuthenticationAndAuthorization;
use function Tests\Helpers\testFormRequestValidations;

uses(RefreshDatabase::class)->group('feature', 'http', 'controllers', 'permissions', 'roles');

beforeEach(function () {
    test()->user = User::factory()->create();

    test()->role = Role::factory()->create();

    Permission::factory(5)
        ->sequence(
            ['name' => PermissionEnum::ROLES_LIST],
            ['name' => PermissionEnum::ROLES_CREATE],
            ['name' => PermissionEnum::ROLES_VIEW],
            ['name' => PermissionEnum::ROLES_UPDATE],
            ['name' => PermissionEnum::ROLES_DELETE],
        )
        ->create();

    $permission = Permission::factory()
        ->create(['name' => PermissionEnum::ROLES_PERMISSIONS]);

    test()->user->givePermissionTo($permission);
});

describe('/roles/{role}/permissions', function () {
    it('returns a no content response', function () {
        $response = actingAs(test()->user)
            ->postJson(route('roles.permissions', test()->role->uuid), [
                'permissions' => [
                    PermissionEnum::ROLES_LIST,
                ],
            ]);

        expect($response->status())->toBe(Response::HTTP_NO_CONTENT)
            ->and($response->headers->get('Content-Type'))->not->toBe('application/json');
    });

    it('assigns the provided permissions to the role', function () {
        actingAs(test()->user)
            ->postJson(route('roles.permissions', test()->role->uuid), [
                'permissions' => [
                    PermissionEnum::ROLES_LIST,
                    PermissionEnum::ROLES_VIEW,
                ],
            ]);

        expect(test()->role->fresh())
            ->permissions->pluck('name')->toArray()->toBe([
                PermissionEnum::ROLES_LIST->value,
                PermissionEnum::ROLES_VIEW->value,
            ]);
    });

    it('removes permissions that are not provided', function () {
        test()->role->givePermissionTo(PermissionEnum::ROLES_DELETE);

        actingAs(test()->user)
            ->postJson(route('roles.permissions', test()->role->uuid), [
                'permissions' => [
                    PermissionEnum::ROLES_LIST,
                    PermissionEnum::ROLES_CREATE,
                    PermissionEnum::ROLES_VIEW,
                    PermissionEnum::ROLES_UPDATE,
                ],
            ]);

        $permissions = test()->role->refresh()->permissions->pluck('name')->toArray();

        expect($permissions)
            ->toHaveCount(4)
            ->not->toContain(PermissionEnum::ROLES_DELETE->value);
    });

    it('replaces the role permissions with the provided permissions', function () {
        test()->role->givePermissionTo([
            PermissionEnum::ROLES_CREATE,
            PermissionEnum::ROLES_UPDATE,
            PermissionEnum::ROLES_DELETE,
        ]);

        actingAs(test()->user)
            ->postJson(route('roles.permissions', test()->role->uuid), [
                'permissions' => [
                    PermissionEnum::ROLES_LIST,
                    PermissionEnum::ROLES_VIEW,
                ],
            ]);

        $permissions = test()->role->refresh()->permissions->pluck('name')->toArray();

        expect($permissions)
            ->toHaveCount(2)
            ->toBe([
                PermissionEnum::ROLES_LIST->value,
                PermissionEnum::ROLES_VIEW->value,
            ])
            ->not->toContain([
                PermissionEnum::ROLES_CREATE->value,
                PermissionEnum::ROLES_UPDATE->value,
                PermissionEnum::ROLES_DELETE->value,
            ]);
    });

    it('removes all permissions when an empty array is provided', function () {
        test()->role->givePermissionTo([
            PermissionEnum::ROLES_CREATE,
            PermissionEnum::ROLES_UPDATE,
            PermissionEnum::ROLES_DELETE,
        ]);

        actingAs(test()->user)
            ->postJson(route('roles.permissions', test()->role->uuid), [
                'permissions' => [],
            ]);

        expect(test()->role->refresh()->permissions->count())->toBe(0);

        testFormRequestValidations(
            method: 'POST',
            route: 'roles.permissions',
            fieldsWithDatasets: [
                'permissions' => 'SyncRolePermissionsController - permissions field',
            ],
            routeParameters: fn () => ['role' => test()->role->uuid]
        );
    });

    testAuthenticationAndAuthorization('POST', 'roles.permissions', fn () => test()->role);
});
