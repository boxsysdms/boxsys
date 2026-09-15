<?php

declare(strict_types=1);

use App\Actions\Roles\UpdateRoleAction;
use App\Data\Roles\UpdateRoleData;
use App\Enums\PermissionScope;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'roles');

beforeEach(function () {
    test()->role = Role::factory()->system()->create(
        [
            'display_name' => ['en' => 'Old Name', 'pt' => 'Nome Antigo'],
            'description' => ['en' => 'Old Description', 'pt' => 'Descrição Antiga'],
        ],
    );
});

describe('update', function () {
    it('updates a role', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(
                displayName: 'Updated Name',
                description: 'Updated Description'
            )
        );

        expect($updated)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->name->toBe('Updated Name')
            ->description->toBe('Updated Description');
    });

    it('updates part of a role', function () {
        $oldDescription = test()->role->description;

        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(
                displayName: 'Partially Updated Name'
            )
        );

        expect($updated)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->name->toBe('Partially Updated Name')
            ->description->toBe($oldDescription);
    });
});

describe('update name', function () {
    beforeEach(function () {
        app()->setLocale('fr');
        app()->setFallbackLocale('pt');
    });

    it('uses the fallback locale display name when display_name is an array with that locale', function () {
        $fallbackLocale = app()->getFallbackLocale();

        $data = new UpdateRoleData(
            displayName: [$fallbackLocale => 'Administrador', 'en' => 'Admin']
        );

        $role = resolve(UpdateRoleAction::class)->handle(test()->role, $data);

        expect($role->name)->toBe('Administrador');
    });

    it('uses display_name when it is a string and the locale matches the fallback locale', function () {
        app()->setLocale(app()->getFallbackLocale());

        $data = new UpdateRoleData(
            displayName: 'Manager'
        );

        $role = resolve(UpdateRoleAction::class)->handle(test()->role, $data);

        expect($role->name)->toBe('Manager');
    });

    it('preserves the name when display_name does not contain the fallback locale', function () {
        $oldName = test()->role->name;

        $data = new UpdateRoleData(
            displayName: ['en' => 'New Name']
        );

        $role = resolve(UpdateRoleAction::class)->handle(test()->role, $data);

        expect($role->name)->toBe($oldName);
    });

    it('preserves the name when display_name is a string and the locale does not match the fallback locale', function () {
        $oldName = test()->role->name;

        $data = new UpdateRoleData(
            displayName: 'New Name'
        );

        $role = resolve(UpdateRoleAction::class)->handle(test()->role, $data);

        expect($role->name)->toBe($oldName);
    });
});

describe('update display_name', function () {
    it('updates display_name with multiple locales', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(displayName: ['en' => 'New Name', 'pt' => 'Novo Nome'])
        );

        expect($updated)
            ->display_name->toBe('New Name')
            ->getTranslations('display_name')->toBe(['en' => 'New Name', 'pt' => 'Novo Nome']);
    });

    it('preserves existing locales when updating display_name with a subset of locales', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(displayName: ['pt' => 'Novo Nome'])
        );

        expect($updated)
            ->display_name->toBe('Old Name')
            ->getTranslations('display_name')->toBe([
                'en' => 'Old Name',
                'pt' => 'Novo Nome',
            ]);
    });

    it('adds new locales when updating display_name with a supported locale', function () {
        config(['boxsys.locales.supported' => ['en', 'pt', 'fr']]);

        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(displayName: ['fr' => 'Nom Français'])
        );

        expect($updated)
            ->display_name->toBe('Old Name')
            ->getTranslations('display_name')->toBe([
                'en' => 'Old Name',
                'pt' => 'Nome Antigo',
                'fr' => 'Nom Français',
            ]);
    });

    it('ignores unsupported locales when updating display_name', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(displayName: ['de' => 'Neuer Name', 'en' => 'New Name'])
        );

        expect($updated)
            ->display_name->toBe('New Name')
            ->getTranslations('display_name')->toBe([
                'en' => 'New Name',
                'pt' => 'Nome Antigo',
            ]);
    });
});

describe('update description', function () {
    it('updates description with multiple locales', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(description: ['en' => 'New Description', 'pt' => 'Nova Descrição'])
        );

        expect($updated)
            ->description->toBe('New Description')
            ->getTranslations('description')->toBe(['en' => 'New Description', 'pt' => 'Nova Descrição']);
    });

    it('preserves existing locales when updating description with a subset of locales', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(description: ['pt' => 'Nova Descrição'])
        );

        expect($updated)
            ->description->toBe('Old Description')
            ->getTranslations('description')->toBe([
                'en' => 'Old Description',
                'pt' => 'Nova Descrição',
            ]);
    });

    it('adds new locales when updating description with a supported locale', function () {
        config(['boxsys.locales.supported' => ['en', 'pt', 'fr']]);

        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(description: ['fr' => 'Nouvelle Description Française'])
        );

        expect($updated)
            ->description->toBe('Old Description')
            ->getTranslations('description')->toBe([
                'en' => 'Old Description',
                'pt' => 'Descrição Antiga',
                'fr' => 'Nouvelle Description Française',
            ]);
    });

    it('ignores unsupported locales when updating description', function () {
        $updated = resolve(UpdateRoleAction::class)->handle(
            test()->role,
            new UpdateRoleData(description: ['de' => 'Nouvelle Description Allemande', 'en' => 'Old Description'])
        );

        expect($updated)
            ->description->toBe('Old Description')
            ->getTranslations('description')->toBe([
                'en' => 'Old Description',
                'pt' => 'Descrição Antiga',
            ]);
    });
});
