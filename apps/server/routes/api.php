<?php

declare(strict_types=1);

use App\Http\Controllers\Permissions;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('permissions', Permissions\ListPermissionsController::class)
        ->name('permissions.index');
});
