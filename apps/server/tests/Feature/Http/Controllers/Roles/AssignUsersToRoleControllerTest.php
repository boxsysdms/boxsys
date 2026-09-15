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

uses(RefreshDatabase::class)->group('feature', 'http', 'controllers', 'roles', 'users');

beforeEach(function () {
    test()->user = User::factory()->create();
    test()->role = Role::factory()->create();

    test()->users = User::factory(5)
        ->sequence(
            ['name' => 'Maria'],
            ['name' => 'Rolando'],
            ['name' => 'Eloah'],
            ['name' => 'Elias'],
            ['name' => 'Jailma'],
        )
        ->create();

    test()->role->users()->attach(test()->users);

    $permission = Permission::factory()
        ->create(['name' => PermissionEnum::ROLES_USERS]);

    test()->user->givePermissionTo($permission);
});

describe('/roles/{role}/users/assign', function () {
    testAuthenticationAndAuthorization('POST', 'roles.users.assign', fn () => test()->role);

    testFormRequestValidations(
        method: 'POST',
        route: 'roles.users.assign',
        fieldsWithDatasets: [
            'users' => 'AssignUsersToRoleController - users field',
        ],
        routeParameters: fn () => ['role' => test()->role->uuid]
    );

    it('returns a no content response', function () {
        $response = actingAs(test()->user)
            ->postJson(route('roles.users.assign', test()->role->uuid), [
                'users' => test()->users->pluck('uuid')->toArray(),
            ]);

        expect($response->status())->toBe(Response::HTTP_NO_CONTENT)
            ->and($response->headers->get('Content-Type'))->not->toBe('application/json');
    });

    it('assigns users by uuid', function () {
        $users = test()->users->pluck('uuid')->all();

        actingAs(test()->user)
            ->postJson(route('roles.users.assign', test()->role->uuid), [
                'users' => $users,
            ]);

        expect(test()->role->fresh())
            ->users->pluck('uuid')->all()->toBe($users);
    });

    it('assigns users by email', function () {
        $users = test()->users->pluck('email')->all();

        actingAs(test()->user)
            ->postJson(route('roles.users.assign', test()->role->uuid), [
                'users' => $users,
            ]);

        expect(test()->role->fresh())
            ->users->pluck('email')->all()->toBe($users);
    });

    it('allows assigning mixed identifiers', function () {
        $users = test()->users->map(function ($user) {
            return fake()->boolean(60) ? $user->uuid : $user->email;
        })->all();

        actingAs(test()->user)
            ->postJson(route('roles.users.assign', test()->role->uuid), [
                'users' => $users,
            ]);

        expect(test()->role->fresh())
            ->users->pluck('uuid')->all()->toBe(test()->users->pluck('uuid')->all());
    });

    it('ignores non‑existent users without error', function () {
        $users = [fake()->uuid(), fake()->safeEmail()];

        $response = actingAs(test()->user)
            ->postJson(route('roles.users.assign', test()->role->uuid), [
                'users' => $users,
            ]);

        expect($response->status())->toBe(Response::HTTP_NO_CONTENT);
    });
});
