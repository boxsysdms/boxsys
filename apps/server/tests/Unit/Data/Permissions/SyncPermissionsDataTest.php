<?php

declare(strict_types=1);

use App\Data\Permissions\SyncPermissionsData;
use App\Enums\Permission as PermissionEnum;

uses()->group('unit', 'data', 'permissions');

describe('instantiation', function () {
    it('accepts mixed permission values', function () {
        $dto = new SyncPermissionsData(
            permissions: [1, 'manage.users', PermissionEnum::ROLES_LIST],
        );

        expect($dto)->toBeInstanceOf(SyncPermissionsData::class);

        expect($dto->permissions)
            ->toHaveCount(3)
            ->and($dto->permissions[0])->toBe(1)
            ->and($dto->permissions[1])->toBe('manage.users')
            ->and($dto->permissions[2])->toBe(PermissionEnum::ROLES_LIST);
    });
});
