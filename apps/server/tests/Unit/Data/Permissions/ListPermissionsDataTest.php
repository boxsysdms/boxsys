<?php

declare(strict_types=1);

use App\Data\Permissions\ListPermissionsData;
use App\Enums\PermissionScope;

uses()->group('unit', 'data', 'permissions');

describe('ListPermissionsData', function () {

    it('can be instantiated', function () {
        $dto = new ListPermissionsData(
            scope: PermissionScope::SYSTEM,
            page: 1,
            perPage: 10,
            sortBy: 'name',
            sortOrder: 'asc'
        );

        expect($dto)->toBeInstanceOf(ListPermissionsData::class);

        expect($dto)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->page->toBe(1)
            ->perPage->toBe(10)
            ->sortBy->toBe('name')
            ->sortOrder->toBe('asc');
    });

    it('uses default values when not provided', function () {
        $dto = new ListPermissionsData(scope: PermissionScope::SYSTEM->value);

        expect($dto)
            ->scope->toBeString()
            ->page->toBeNull()
            ->perPage->toBeNull()
            ->sortBy->toBe('name')
            ->sortOrder->toBe('asc');
    });
});
