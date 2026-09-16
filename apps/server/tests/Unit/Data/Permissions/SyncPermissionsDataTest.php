<?php

declare(strict_types=1);

use App\Data\Permissions\SyncPermissionsData;
use App\Enums\Permission;

uses()->group('unit', 'data', 'permissions');

describe('instantiation', function () {
    it('accepts permissions with different value types', function () {
        $dto = new SyncPermissionsData(
            permissions: [1, 'manage.users', Permission::ROLES_LIST],
        );

        expect($dto->permissions)
            ->toHaveCount(3)
            ->and($dto->permissions[0])->toBe(1)
            ->and($dto->permissions[1])->toBe('manage.users')
            ->and($dto->permissions[2])->toBe(Permission::ROLES_LIST);
    });
});
