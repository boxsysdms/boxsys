<?php

declare(strict_types=1);

use App\Enums\PermissionScope;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Tests\Helpers\testAuthenticationAndAuthorization;
use function Tests\Helpers\testFormRequestValidations;
use function Tests\Helpers\testPaginationParameters;

uses(RefreshDatabase::class)->group('feature', 'http', 'controllers', 'permissions');

beforeEach(function () {
    test()->user = User::factory()->create();
});

/* --- Tests for ListPermissionsController::__invoke() --- */

describe('GET /permissions', function () {
    testAuthenticationAndAuthorization('GET', 'permissions.index', withAuthorization: false);

    it('returns only permissions matching the requested scope', function () {
        Permission::factory()
            ->count(5)
            ->sequence(
                ['scope' => PermissionScope::SYSTEM],
                ['scope' => PermissionScope::COLLECTION],
                ['scope' => PermissionScope::SYSTEM],
                ['scope' => PermissionScope::SYSTEM],
                ['scope' => PermissionScope::COLLECTION],
            )
            ->create();

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response)->status()->toBe(Response::HTTP_OK);

        expect($response->json('data'))->toHaveCount(3);
    });

    it('includes links and meta information', function () {
        Permission::factory()->count(5)->create();

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response->json())->toHaveKeys(['links', 'meta']);
    });

    it('returns an empty list when there are no permissions', function () {
        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response)->status()->toBe(Response::HTTP_OK);

        expect($response->json('data'))->toHaveCount(0);
    });

    it('sorts permissions by name', function () {
        Permission::factory()->createMany([
            ['name' => 'users.create', 'scope' => PermissionScope::SYSTEM],
            ['name' => 'groups.add', 'scope' => PermissionScope::SYSTEM],
            ['name' => 'collections.manage', 'scope' => PermissionScope::SYSTEM],
        ]);

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM, 'sort' => 'name']));

        expect($response->json('data.0.id'))->toBe('collections.manage')
            ->and($response->json('data.1.id'))->toBe('groups.add')
            ->and($response->json('data.2.id'))->toBe('users.create');
    });

    it('paginates permissions', function () {
        Permission::factory()->count(50)->create(['scope' => PermissionScope::SYSTEM]);

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', [
                'scope' => PermissionScope::SYSTEM,
                'page' => 2,
                'perPage' => 20,
            ]));

        expect($response->json('data'))
            ->toHaveCount(20)
            ->and($response->json('meta.current_page'))->toBe(2)
            ->and($response->json('meta.per_page'))->toBe(20);
    });

    it('returns localized descriptions', function () {
        Permission::factory()->count(2)->createMany([
            [
                'name' => 'users.create',
                'scope' => PermissionScope::SYSTEM,
                'description' => ['en' => 'Create users', 'pt' => 'Criar utilizadores'],
            ],
            [
                'name' => 'groups.users.add',
                'scope' => PermissionScope::SYSTEM,
                'description' => ['en' => 'Add users to groups', 'pt' => 'Adicionar utilizadores a grupos'],
            ],
        ]);

        $response = actingAs(test()->user)
            ->withHeaders(['Accept-Language' => 'pt'])
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        $descriptions = $response->json('data.*.attributes.description');

        expect($descriptions)->toContain('Criar utilizadores', 'Adicionar utilizadores a grupos');
    });

    testFormRequestValidations('GET', 'permissions.index', [
        'scope' => 'scope filter values',
    ]);

    testPaginationParameters('permissions.index', maxPerPage: 100);
});
