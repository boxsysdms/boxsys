<?php

declare(strict_types=1);

use App\Data\Roles\ListRolesData;
use App\Enums\PermissionScope;

uses()->group('unit', 'data', 'roles');

describe('instantiation', function () {

    it('can be instantiated with valid data', function () {
        $dto = new ListRolesData(
            scope: PermissionScope::SYSTEM,
            search: 'admin',
            sortBy: 'name',
            sortOrder: 'asc',
            page: 1,
            perPage: 15,
        );

        expect($dto)->toBeInstanceOf(ListRolesData::class);

        expect($dto)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->search->toBe('admin')
            ->sortBy->toBe('name')
            ->sortOrder->toBe('asc')
            ->page->toBe(1)
            ->perPage->toBe(15);
    });

    it('uses default values for optional fields', function () {
        $dto = new ListRolesData(scope: PermissionScope::COLLECTION->value);

        expect($dto)
            ->scope->toBe(PermissionScope::COLLECTION->value)
            ->search->toBeNull()
            ->sortBy->toBe('display_name')
            ->sortOrder->toBe('asc')
            ->page->toBeNull()
            ->perPage->toBeNull();
    });
});
