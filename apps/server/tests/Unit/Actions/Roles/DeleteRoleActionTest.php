<?php

declare(strict_types=1);

use App\Actions\Roles\DeleteRoleAction;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('unit', 'actions', 'roles');

describe('deletion', function () {
    it('soft deletes a role', function () {
        $role = Role::factory()->create();

        $deleted = resolve(DeleteRoleAction::class)->handle($role);

        expect($deleted)->toBeTrue();

        expect(Role::query()->find($role->id))->toBeNull();
        expect(Role::query()->withTrashed()->find($role->id))->not->toBeNull();
    });
});
