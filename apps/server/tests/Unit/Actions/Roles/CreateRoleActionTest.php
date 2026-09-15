<?php

declare(strict_types=1);

use App\Actions\Roles\CreateRoleAction;
use App\Data\Roles\CreateRoleData;
use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'roles');

describe('creation', function () {
    it('creates a role successfully', function () {
        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: 'Admin',
            description: 'Administrator role'
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role)->toBeInstanceOf(Role::class)
            ->and($role->scope)->toBe(PermissionScope::SYSTEM)
            ->and($role->name)->toBe('Admin')
            ->and($role->description)->toBe('Administrator role');
    });

    it('creates a role without a deletion timestamp', function () {
        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: 'Active Role',
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role)
            ->created_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
            ->updated_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
            ->deleted_at->toBeNull();
    });
});

describe('name', function () {
    beforeEach(function () {
        app()->setLocale('fr');
        app()->setFallbackLocale('pt');
    });

    it('uses the fallback locale display name when display_name is an array with that locale', function () {
        $fallbackLocale = app()->getFallbackLocale();

        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: [$fallbackLocale => 'Administrador', 'en' => 'Admin']
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role->name)->toBe('Administrador');
    });

    it('uses the first display name when display_name is an array without the default locale', function () {
        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: ['fr' => 'Administrateur']
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role->name)->toBe('Administrateur');
    });

    it('uses display_name when it is a string', function () {
        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: 'Manager'
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role->name)->toBe('Manager');
    });
});

describe('description', function () {
    it('creates a role with a translated description', function () {
        $data = new CreateRoleData(
            scope: PermissionScope::SYSTEM,
            displayName: 'Admin',
            description: ['pt' => 'Função de administrador', 'en' => 'Administrator role']
        );

        $role = resolve(CreateRoleAction::class)->handle($data);

        expect($role->description)->toBe('Administrator role');
    });
});
