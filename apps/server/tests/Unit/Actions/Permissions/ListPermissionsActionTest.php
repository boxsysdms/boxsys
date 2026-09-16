<?php

declare(strict_types=1);

use App\Actions\Permissions\ListPermissionsAction;
use App\Data\Permissions\ListPermissionsData;
use App\Enums\PermissionScope;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'permissions');

describe('listing permissions', function () {
    it('returns only permissions for the specified scope', function () {
        Permission::factory(3)->system()->create();
        Permission::factory(2)->collection()->create();

        $result = resolve(ListPermissionsAction::class)
            ->handle(
                new ListPermissionsData(PermissionScope::COLLECTION)
            );

        expect($result)->toHaveCount(2);
    });

    it('returns an empty list when no permissions exists for the specified scope', function () {
        $result = resolve(ListPermissionsAction::class)
            ->handle(
                new ListPermissionsData(PermissionScope::SYSTEM)
            );

        expect($result)->toHaveCount(0);
    });

    it('returns permissions sorted by name in ascending order', function () {
        Permission::factory(3)
            ->system()
            ->sequence(
                ['name' => 'Mango'],
                ['name' => 'Zebra'],
                ['name' => 'Apple'],
            )
            ->create();

        $result = resolve(ListPermissionsAction::class)
            ->handle(
                new ListPermissionsData(PermissionScope::SYSTEM)
            );

        expect($result[0]->name)->toBe('Apple')
            ->and($result[1]->name)->toBe('Mango')
            ->and($result[2]->name)->toBe('Zebra');
    });

    it('returns paginated results', function () {
        Permission::factory(15)->system()->create();

        $result = resolve(ListPermissionsAction::class)
            ->handle(
                new ListPermissionsData(
                    scope: PermissionScope::SYSTEM,
                    page: 2,
                    perPage: 5
                )
            );

        expect($result)->toBeInstanceOf(Illuminate\Pagination\LengthAwarePaginator::class);

        expect($result)
            ->total()->toBe(15)
            ->perPage()->toBe(5)
            ->currentPage()->toBe(2)
            ->count()->toBe(5)
            ->first()->toBeInstanceOf(Permission::class);
    });
});
