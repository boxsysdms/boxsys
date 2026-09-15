<?php

declare(strict_types=1);

use App\Actions\Roles\ListRolesAction;
use App\Data\Roles\ListRolesData;
use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'roles');

describe('listing', function () {
    it('lists roles only from the requested scope', function () {
        Role::factory()->system()->count(2)->create();
        Role::factory()->collection()->count(3)->create();

        $result = resolve(ListRolesAction::class)->handle(
            new ListRolesData(scope: PermissionScope::SYSTEM)
        );

        expect($result->total())->toBe(2);
    });

    it('returns an empty result when no roles match the requested scope', function () {
        Role::factory()->collection()->count(3)->create();

        $result = resolve(ListRolesAction::class)->handle(
            new ListRolesData(scope: PermissionScope::SYSTEM)
        );

        expect($result->total())->toBe(0);
    });
});

describe('filtering', function () {
    beforeEach(function () {
        app()->setLocale('pt');
        app()->setFallbackLocale('en');

        Role::factory(3)
            ->collection()
            ->sequence(
                [
                    'name' => 'Manager',
                    'display_name' => ['en' => 'Manager', 'pt' => 'Gerente'],
                    'description' => [
                        'en' => 'Manage a entire collection',
                        'pt' => 'Gerenciar toda a coleção',
                    ],
                ],
                [
                    'name' => 'User',
                    'display_name' => ['en' => 'User', 'pt' => 'Utilizador'],
                    'description' => [
                        'en' => 'Regular user of the collection',
                        'pt' => 'Utilizador regular da coleção',
                    ],
                ],
                [
                    'name' => 'Viewer',
                    'display_name' => ['en' => 'Viewer', 'pt' => 'Visualizador'],
                    'description' => [
                        'en' => 'Read-only access to the collection',
                        'pt' => 'Acesso somente leitura à coleção',
                    ],
                ]
            )
            ->create();

        Role::factory()->system()->create(
            [
                'name' => 'Administrator',
                'display_name' => ['en' => 'Administrator', 'pt' => 'Administrador'],
                'description' => [
                    'en' => 'Full access to the system and read-only access to the collection',
                    'pt' => 'Acesso total ao sistema incluindo leitura somente leitura à coleção',
                ],
            ]
        );
    });

    it('filters roles by display name using the current locale', function () {
        $data = new ListRolesData(
            scope: PermissionScope::COLLECTION,
            search: 'GERENTE',
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->display_name)->toBe('Gerente');
    });

    it('filters roles by description using the current locale', function () {
        $data = new ListRolesData(
            scope: PermissionScope::COLLECTION,
            search: 'LEITURA',
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->total())->toBe(1)
            ->and($result->items()[0]->display_name)->toBe('Visualizador');
    });

    it('does not find roles by display name in another locale', function () {
        $data = new ListRolesData(
            scope: PermissionScope::COLLECTION,
            search: 'MANAGER',
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->total())->toBe(0);
    });

    it('does not find roles by description in another locale', function () {
        $data = new ListRolesData(
            scope: PermissionScope::COLLECTION,
            search: 'READ-ONLY',
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->total())->toBe(0);
    });
});

describe('sorting', function () {
    beforeEach(function () {
        Role::factory(3)
            ->system()
            ->sequence(
                [
                    'display_name' => ['en' => 'CCC', 'pt' => 'FFF'],
                    'description' => ['en' => 'CCC', 'pt' => 'FFF'],
                ],
                [
                    'display_name' => ['pt' => 'EEE', 'en' => 'AAA'],
                    'description' => ['pt' => 'EEE', 'en' => 'AAA'],
                ],
                [
                    'display_name' => ['fr' => 'xxx', 'en' => 'BBB', 'pt' => 'DDD'],
                    'description' => ['fr' => 'xxx', 'en' => 'BBB', 'pt' => 'DDD'],
                ],
            )
            ->create();
    });

    it('sorts roles by display name in the current locale by default', function () {
        $data = new ListRolesData(
            scope: PermissionScope::SYSTEM
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->items()[0]->display_name)->toBe('AAA')
            ->and($result->items()[1]->display_name)->toBe('BBB')
            ->and($result->items()[2]->display_name)->toBe('CCC');
    });

    it('sorts roles by description in the current locale in descending order', function () {
        app()->setLocale('pt');

        $data = new ListRolesData(
            scope: PermissionScope::SYSTEM,
            sortBy: 'description',
            sortOrder: 'desc'
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result->items()[0]->description)->toBe('FFF')
            ->and($result->items()[1]->description)->toBe('EEE')
            ->and($result->items()[2]->description)->toBe('DDD');
    });
});

describe('pagination', function () {
    beforeEach(function () {
        Role::factory(20)->system()->create();
    });

    it('returns paginated roles correctly', function () {
        $data = new ListRolesData(scope: PermissionScope::SYSTEM);

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result)
            ->items()->toHaveCount(15)
            ->total()->toBe(20)
            ->perPage()->toBe(15)
            ->currentPage()->toBe(1)
            ->count()->toBe(15)
            ->first()->toBeInstanceOf(Role::class);
    });

    it('applies custom pagination settings', function () {
        $data = new ListRolesData(
            scope: PermissionScope::SYSTEM,
            page: 2,
            perPage: 5
        );

        $result = resolve(ListRolesAction::class)->handle($data);

        expect($result)
            ->total()->toBe(20)
            ->perPage()->toBe(5)
            ->currentPage()->toBe(2)
            ->count()->toBe(5);
    });
});
