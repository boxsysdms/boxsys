<?php

declare(strict_types=1);

use App\Data\Permissions\ListPermissionsData;
use App\Enums\PermissionScope;

uses()->group('unit', 'data', 'permissions');

describe('instantiation', function () {
    it('can be instantiated', function () {
        $dto = new ListPermissionsData(
            scope: PermissionScope::SYSTEM,
            page: 1,
            perPage: 10,
        );

        expect($dto)
            ->scope->toBe(PermissionScope::SYSTEM)
            ->page->toBe(1)
            ->perPage->toBe(10);
    });

    it('uses null for optional pagination values when not provided', function () {
        $dto = new ListPermissionsData(scope: PermissionScope::SYSTEM->value);

        expect($dto)
            ->page->toBeNull()
            ->perPage->toBeNull();
    });
});
