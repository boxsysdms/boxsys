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
        Permission::factory(3)->system()->create();
        Permission::factory(2)->collection()->create();

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response)
            ->status()->toBe(Response::HTTP_OK)
            ->headers->get('Content-Type')->toBe('application/json');

        expect($response->json('data'))->toHaveCount(3);
    });

    it('returns an empty list when there are no permissions matching the requested scope', function () {
        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response)->status()->toBe(Response::HTTP_OK);

        expect($response->json('data'))->toHaveCount(0);
    })->depends('it returns only permissions matching the requested scope');

    it('returns permissions with the expected structure', function () {
        Permission::factory()->system()->create();

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response->json())->toHaveKeys(['data', 'meta', 'links']);

        expect($response->json('data.0'))
            ->toHaveKeys(['name', 'description'])
            ->not->toHaveKeys(['id', 'scope']);
    })->depends('it returns only permissions matching the requested scope');

    it('returns permissions sorted by name ascending', function () {
        Permission::factory(3)
            ->system()
            ->sequence(
                ['name' => 'users.create'],
                ['name' => 'groups.add'],
                ['name' => 'collections.manage'],
            )
            ->create();

        $response = actingAs(test()->user)
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        expect($response->json('data.0.name'))->toBe('collections.manage')
            ->and($response->json('data.1.name'))->toBe('groups.add')
            ->and($response->json('data.2.name'))->toBe('users.create');
    })->depends('it returns only permissions matching the requested scope');

    it('returns the requested page with the configured page size', function () {
        Permission::factory(50)->system()->create();

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
    })->depends('it returns only permissions matching the requested scope');

    it('returns descriptions translated to the requested locale', function () {
        Permission::factory(2)
            ->system()
            ->sequence(
                [
                    'name' => 'users.create',
                    'description' => ['en' => 'Create users', 'pt' => 'Criar utilizadores'],
                ],
                [
                    'name' => 'groups.users.add',
                    'description' => ['en' => 'Add users to groups', 'pt' => 'Adicionar utilizadores a grupos'],
                ],
            )
            ->create();

        $response = actingAs(test()->user)
            ->withHeaders(['Accept-Language' => 'pt'])
            ->getJson(route('permissions.index', ['scope' => PermissionScope::SYSTEM]));

        $descriptions = $response->json('data.*.description');

        expect($descriptions)->toContain('Criar utilizadores', 'Adicionar utilizadores a grupos');
    })->depends('it returns only permissions matching the requested scope');

    testFormRequestValidations('GET', 'permissions.index', [
        'scope' => 'scope filter values',
    ]);

    testPaginationParameters('permissions.index', maxPerPage: 100, hasSort: false);
});
