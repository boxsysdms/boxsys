<?php

declare(strict_types=1);

use App\Http\Controllers\Permissions;
use App\Http\Controllers\Roles;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // --- Permissions ---

    Route::get('permissions', Permissions\ListPermissionsController::class)
        ->name('permissions.index');

    // --- Roles ---

    Route::apiResource('roles', Roles\RoleController::class);

    Route::post('roles/{role}/permissions', Roles\SyncRolePermissionsController::class)
        ->name('roles.permissions');

    Route::post('roles/{role}/users/assign', Roles\AssignUsersToRoleController::class)
        ->name('roles.users.assign');

    Route::post('roles/{role}/users/remove', Roles\RemoveUsersFromRoleController::class)
        ->name('roles.users.remove');
});
