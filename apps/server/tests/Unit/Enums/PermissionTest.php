<?php

declare(strict_types=1);

use App\Enums\Permission;

uses()->group('unit', 'enums', 'permission');

describe('scope', function () {
    it('provides a scope method', function () {
        expect(method_exists(Permission::cases()[0], 'scope'))->toBeTrue();
    });

    it('returns the system scope', function () {
        $permission = Permission::ROLES_LIST;

        expect($permission->scope())->toBe('system');
    });

    it('returns the collection scope', function () {
        $permission = Permission::TEST_SCOPE;

        expect($permission->scope())->toBe('collection');
    });
});
