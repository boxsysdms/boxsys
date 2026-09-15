<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Enums\PermissionScope;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Tests\Helpers\testAuthenticationAndAuthorization;
use function Tests\Helpers\testFormRequestValidations;
use function Tests\Helpers\testPaginationParameters;

uses(RefreshDatabase::class)->group('feature', 'http', 'controllers', 'roles');

beforeEach(function () {
    app()->setLocale('en');

    test()->user = User::factory()->create();

    $permissions = Permission::factory(5)
        ->system()
        ->sequence(
            ['name' => PermissionEnum::ROLES_LIST->value],
            ['name' => PermissionEnum::ROLES_VIEW->value],
            ['name' => PermissionEnum::ROLES_CREATE->value],
            ['name' => PermissionEnum::ROLES_UPDATE->value],
            ['name' => PermissionEnum::ROLES_DELETE->value],
        )
        ->create();

    test()->user->givePermissionTo($permissions);
});

/**
 * --------------------------------------
 * --- Tests for RoleController@index ---
 * --------------------------------------
 */
describe('GET /roles', function () {
    beforeEach(function () {
        Role::factory(5)
            ->system()
            ->sequence(
                ['display_name' => 'User', 'description' => 'User role'],
                ['display_name' => 'Manager', 'description' => 'Manager role'],
                ['display_name' => 'Administrator', 'description' => 'Administrator role'],
                ['display_name' => 'Guest', 'description' => 'Guest role'],
                ['display_name' => 'Collaborator', 'description' => 'Collaborator role'],
            )
            ->create();
    });

    it('should have application/json content type header', function () {
        actingAs(test()->user)
            ->getJson(route('roles.index', ['scope' => PermissionScope::SYSTEM]))
            ->assertHeader('Content-Type', 'application/json');
    });

    it('should return 200 status code', function () {
        actingAs(test()->user)
            ->getJson(route('roles.index', ['scope' => PermissionScope::SYSTEM]))
            ->assertStatus(Response::HTTP_OK);
    });

    it('should return roles in the correct format', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response->json())->toHaveKeys(['data', 'meta', 'links']);

        expect($response->json('data'))->toHaveCount(5);

        expect($response->json('data.0'))
            ->toHaveKeys([
                'uuid',
                'scope',
                'displayName',
                'description',
                'permissionsCount',
                'usersCount',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->not->toHaveKeys(['id', 'name']);
    });

    it('sort by displayName by default', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.index', ['scope' => PermissionScope::SYSTEM]));

        $roleDisplayNames = array_column($response->json('data'), 'displayName');

        expect($roleDisplayNames)->toBe(['Administrator', 'Collaborator', 'Guest', 'Manager', 'User']);
    });

    it('can filter roles', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.index', [
                'scope' => PermissionScope::SYSTEM,
                'search' => 'ADMIN',
            ]));

        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.displayName'))->toBe('Administrator');
    });

    it('returns an empty result when no roles match the search', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.index', [
                'scope' => PermissionScope::SYSTEM,
                'search' => 'NON_EXISTENT_ROLE',
            ]));

        expect($response->json('data'))->toHaveCount(0);
    });

    testAuthenticationAndAuthorization('GET', 'roles.index');

    testFormRequestValidations('GET', 'roles.index', [
        'search' => 'RoleController@index - search field',
    ]);

    testPaginationParameters('roles.index');
});

/**
 * --------------------------------------
 * --- Tests for RoleController@store ---
 * --------------------------------------
 */
describe('POST /roles', function () {
    beforeEach(function () {
        test()->role = Role::factory()->system()->make();
    });

    it('returns an application/json content type header', function () {
        $response = actingAs(test()->user)
            ->postJson(route('roles.store'), [
                'scope' => test()->role->scope,
                'displayName' => test()->role->display_name,
                'description' => test()->role->description,
            ]);

        expect($response->status())->toBe(Response::HTTP_CREATED)
            ->and($response->headers->get('Content-Type'))->toBe('application/json');
    });

    it('creates a new role in the database', function () {
        actingAs(test()->user)
            ->postJson(route('roles.store'), [
                'scope' => test()->role->scope,
                'displayName' => test()->role->display_name,
                'description' => test()->role->description,
            ]);

        expect(Role::count())->toBe(1);

        expect(Role::first())
            ->name->toBe(test()->role->display_name)
            ->display_name->toBe(test()->role->display_name)
            ->description->toBe(test()->role->description)
            ->users_count->toBe(0)
            ->permissions_count->toBe(0)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull()
            ->deleted_at->toBeNull()
            ->getTranslations('display_name')->toBe(
                test()->role->getTranslations('display_name', ['en'])
            )
            ->getTranslations('description')->toBe(
                test()->role->getTranslations('description', ['en'])
            );
    });

    it('persists multilingual translations for display name and description', function () {
        actingAs(test()->user)
            ->postJson(route('roles.store'), [
                'scope' => test()->role->scope,
                'displayName' => [
                    'en' => test()->role->getTranslation('display_name', 'en'),
                    'pt' => test()->role->getTranslation('display_name', 'pt'),
                ],
                'description' => [
                    'en' => test()->role->getTranslation('description', 'en'),
                    'pt' => test()->role->getTranslation('description', 'pt'),
                ],
            ]);

        expect(Role::count())->toBe(1);

        expect(Role::first())
            ->name->toBe(test()->role->display_name)
            ->display_name->toBe(test()->role->display_name)
            ->description->toBe(test()->role->description)
            ->getTranslations('display_name')->toBe(
                test()->role->getTranslations('display_name')
            )
            ->getTranslations('description')->toBe(
                test()->role->getTranslations('description')
            );
    });

    it('returns the correct resource structure and filters internal attributes', function () {
        $response = actingAs(test()->user)
            ->postJson(route('roles.store'), [
                'scope' => test()->role->scope,
                'displayName' => test()->role->display_name,
                'description' => test()->role->description,
            ]);

        expect($response->json('data'))
            ->toHaveKeys([
                'uuid',
                'scope',
                'displayName',
                'description',
                'usersCount',
                'permissionsCount',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->not->toHaveKeys(['id', 'guard_name', 'guardName']);

        expect(test()->role)
            ->display_name->toBe($response->json('data.displayName'))
            ->description->toBe($response->json('data.description'));
    });

    testAuthenticationAndAuthorization('POST', 'roles.store');

    testFormRequestValidations(
        method: 'POST',
        route: 'roles.store',
        fieldsWithDatasets: [
            'scope' => 'RoleController@store - scope field',
            'displayName' => 'RoleController@store - displayName field',
            'description' => 'RoleController@store - description field',
        ],
        alwaysIncludedData: ['scope' => PermissionScope::SYSTEM->value],
    );
});

/**
 * --------------------------------------
 * --- Tests for RoleController@show ---
 * --------------------------------------
 */
describe('GET /roles/{role}', function () {
    beforeEach(fn () => test()->role = Role::factory()->create());

    it('returns an application/json content type header', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.show', test()->role->uuid));

        expect($response->status())->toBe(Response::HTTP_OK)
            ->and($response->headers->get('Content-Type'))->toBe('application/json');
    });

    it('returns the correct resource structure and filters internal attributes', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.show', test()->role->uuid));

        expect($response->json('data'))
            ->toHaveKeys(['scope', 'displayName', 'description', 'deletedAt'])
            ->not->toHaveKeys(['id', 'guard_name', 'guardName']);

        expect(test()->role)
            ->display_name->toBe($response->json('data.displayName'))
            ->description->toBe($response->json('data.description'));
    });

    it('maps all model attributes correctly into the payload', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.show', test()->role->uuid));

        test()->role->refresh();

        expect(test()->role)
            ->uuid->toBe($response->json('data.uuid'))
            ->scope->value->toBe($response->json('data.scope'))
            ->display_name->toBe($response->json('data.displayName'))
            ->description->toBe($response->json('data.description'))
            ->created_at->toISOString()->toBe($response->json('data.createdAt'))
            ->updated_at->toISOString()->toBe($response->json('data.updatedAt'))
            ->deleted_at->toBeNull();

        $usersCount = test()->role->users()->count();
        $permissionsCount = test()->role->permissions()->count();

        expect($response->json('data.usersCount'))->toBe($usersCount)
            ->and($response->json('data.permissionsCount'))->toBe($permissionsCount);
    });

    it('fails when lookup target cannot be found', function () {
        $response = actingAs(test()->user)
            ->getJson(route('roles.show', 'non-existent-uuid'));

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND);
    });

    it('excludes soft-deleted items from normal queries', function () {
        test()->role->delete();

        $response = actingAs(test()->user)
            ->getJson(route('roles.show', test()->role->uuid));

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND);
    });

    testAuthenticationAndAuthorization('GET', 'roles.show', fn () => ['role' => test()->role->uuid]);
});

/**
 * ---------------------------------------
 * --- Tests for RoleController@update ---
 * ---------------------------------------
 */
describe('PUT /roles/{role}', function () {
    beforeEach(function () {
        test()->role = Role::factory()
            ->system()
            ->create([
                'display_name' => ['en' => 'Old Name', 'pt' => 'Nome Antigo'],
                'description' => ['en' => 'Old Description', 'pt' => 'Descrição Antiga'],
            ]);
    });

    it('returns an application/json content type header', function () {
        $response = actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid), [
                'displayName' => test()->role->display_name,
                'description' => test()->role->description,
            ]);

        expect($response->status())->toBe(Response::HTTP_OK)
            ->and($response->headers->get('Content-Type'))->toBe('application/json');
    });

    it('updates an existing role in the database', function () {
        actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid), [
                'displayName' => 'New Name',
                'description' => 'New Description',
            ]);

        expect(Role::count())->toBe(1);

        expect(Role::first())
            ->name->toBe('New Name')
            ->display_name->toBe('New Name')
            ->description->toBe('New Description')
            ->users_count->toBe(0)
            ->permissions_count->toBe(0)
            ->created_at->not->toBeNull()
            ->updated_at->not->toBeNull()
            ->deleted_at->toBeNull()
            ->getTranslations('display_name')->toBe(
                ['en' => 'New Name', 'pt' => 'Nome Antigo']
            )
            ->getTranslations('description')->toBe(
                ['en' => 'New Description', 'pt' => 'Descrição Antiga']
            );
    });

    it('persists multilingual translations for display name and description', function () {
        actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid), [
                'displayName' => [
                    'en' => 'New Name',
                    'pt' => 'Nome Antigo',
                ],
                'description' => [
                    'en' => 'New Description',
                    'pt' => 'Descrição Antiga',
                ],
            ]);

        expect(Role::count())->toBe(1);

        expect(Role::first())
            ->name->toBe('New Name')
            ->display_name->toBe('New Name')
            ->description->toBe('New Description')
            ->getTranslations('display_name')->toBe(
                ['en' => 'New Name', 'pt' => 'Nome Antigo']
            )
            ->getTranslations('description')->toBe(
                ['en' => 'New Description', 'pt' => 'Descrição Antiga']
            );
    });

    it('returns the correct resource structure and filters internal attributes', function () {
        $response = actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid), [
                'displayName' => 'New Name',
                'description' => 'New Description',
            ]);

        expect($response->json('data'))
            ->toHaveKeys([
                'uuid',
                'scope',
                'displayName',
                'description',
                'usersCount',
                'permissionsCount',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->not->toHaveKeys(['id', 'guard_name', 'guardName']);
    });

    it('ignores updates to the scope field', function () {
        actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid), [
                'scope' => PermissionScope::COLLECTION->value,
            ]);

        expect(Role::first()->scope)->toBe(test()->role->scope);
    });

    it('preserves the updated_at timestamp when no attributes change', function () {
        $oldUpdatedAt = test()->role->updated_at->toISOString();

        actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid));

        expect(Role::first()->updated_at->toISOString())
            ->toBe($oldUpdatedAt);
    });

    it('fails when lookup target cannot be found', function () {
        $response = actingAs(test()->user)
            ->putJson(route('roles.update', 'non-existent-uuid'));

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND);
    });

    it('excludes soft-deleted items from normal queries', function () {
        test()->role->delete();

        $response = actingAs(test()->user)
            ->putJson(route('roles.update', test()->role->uuid));

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND);
    });

    testAuthenticationAndAuthorization('PUT', 'roles.update', fn () => ['role' => test()->role->uuid]);

    testFormRequestValidations(
        method: 'PUT',
        route: 'roles.update',
        fieldsWithDatasets: [
            'displayName' => 'RoleController@update - displayName field',
            'description' => 'RoleController@update - description field',
        ],
        routeParameters: fn () => ['role' => test()->role->uuid]
    );
});

/**
 * ---------------------------------------
 * --- Tests for RoleController@delete ---
 * ---------------------------------------
 */
describe('DELETE /roles/{role}', function () {
    beforeEach(fn () => test()->role = Role::factory()->create());

    it('fails when lookup target cannot be found', function () {
        $response = actingAs(test()->user)
            ->deleteJson(route('roles.destroy', 'non-existent-uuid'));

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND);
    });

    it('should delete the role', function () {
        actingAs(test()->user)
            ->deleteJson(route('roles.destroy', ['role' => test()->role->uuid]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(Role::count())->toBe(0)
            ->and(Role::withTrashed()->count())->toBe(1);
    });

    testAuthenticationAndAuthorization(
        'DELETE',
        'roles.destroy',
        fn () => ['role' => test()->role->uuid]
    );
});
