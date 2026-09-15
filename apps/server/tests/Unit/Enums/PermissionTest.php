<?php

declare(strict_types=1);

use App\Enums\Permission;

uses()->group('unit', 'enums', 'permission');

describe('Permission Enum', function () {
    it('should have a scope method', function () {
        expect(method_exists(Permission::cases()[0], 'scope'))->toBeTrue();
    });

    it('return system scope', function () {
        $permission = Permission::ROLES_LIST;

        expect($permission->scope())->toBe('system');
    });

    it('return collection scope', function () {
        $permission = Permission::TEST_SCOPE;

        expect($permission->scope())->toBe('collection');
    });
});
