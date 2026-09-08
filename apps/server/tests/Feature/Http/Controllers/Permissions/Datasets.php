<?php

declare(strict_types=1);

use App\Enums\PermissionScope;

dataset('scope filter values', [
    'null' => [null, fn () => __('validation.required', ['attribute' => 'scope'])],
    'empty' => ['', fn () => __('validation.required', ['attribute' => 'scope'])],
    'system' => [PermissionScope::SYSTEM],
    'collection' => [PermissionScope::COLLECTION],
    'invalid' => ['invalid', fn () => __('validation.in', ['attribute' => 'scope'])],
]);
