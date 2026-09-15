<?php

declare(strict_types=1);

use App\Models\User;

/**
 * Base datasets for testing the 'displayName' field validation rules on the
 * RoleController endpoints.
 */
$displayNameTestData = [
    'string' => ['Valid Display Name', null],

    'array' => [
        ['displayName' => ['en' => 'Valid Display Name', 'pt' => 'Nome de Exibição Válido'], 'x' => null],
        null,
    ],

    'null' => [
        null,
        fn () => __('validation.required', ['attribute' => 'displayName']),
    ],

    'empty string' => [
        '',
        fn () => __('validation.required', ['attribute' => 'displayName']),
    ],

    'empty array' => [
        [],
        fn () => __('validation.required', ['attribute' => 'displayName']),
    ],

    'string below minimum length' => [
        'ab',
        fn () => __('validation.translatable.string.min', ['attribute' => 'displayName', 'min' => 3]),
    ],

    'string at minimum length' => ['abc', null],

    'string at maximum length' => [str_repeat('a', 100), null],

    'string above maximum length' => [
        str_repeat('a', 101),
        fn () => __('validation.translatable.string.max', ['attribute' => 'displayName', 'max' => 100]),
    ],

    'array with minimum items' => [
        ['displayName' => generateTranslatableArray(1), 'x' => null],
        null,
    ],

    'array with maximum items' => [
        ['displayName' => generateTranslatableArray(25), 'x' => null],
        null,
    ],

    'array above maximum items' => [
        ['displayName' => generateTranslatableArray(26), 'x' => null],
        fn () => __('validation.translatable.array.max', ['attribute' => 'displayName', 'max' => 25]),
    ],

    'translation below minimum length' => [
        ['en' => 'ab'],
        fn () => __('validation.translatable.string.min', ['attribute' => 'displayName', 'min' => 3]),
    ],

    'translation at minimum length' => [
        ['en' => 'abc'],
        null,
    ],

    'translation at maximum length' => [
        ['en' => str_repeat('a', 100)],
        null,
    ],

    'translation above maximum length' => [
        ['en' => str_repeat('a', 101)],
        fn () => __('validation.translatable.string.max', ['attribute' => 'displayName', 'max' => 100]),
    ],
];

/**
 * Base datasets for testing the 'description' field validation rules on the
 * RoleController endpoints.
 */
$descriptionTestData = [
    'string' => ['Valid Description', null],

    'array' => [
        ['description' => ['en' => 'Valid Description', 'pt' => 'Descrição Válida'], 'x' => null],
        null,
    ],

    'null' => [null, null],

    'empty string' => ['', null],

    'empty array' => [[], null],

    'string below minimum length' => [
        'ab',
        fn () => __('validation.translatable.string.min', ['attribute' => 'description', 'min' => 3]),
    ],

    'string at minimum length' => ['abc', null],

    'string at maximum length' => [str_repeat('a', 100), null],

    'string above maximum length' => [
        str_repeat('a', 101),
        fn () => __('validation.translatable.string.max', ['attribute' => 'description', 'max' => 100]),
    ],

    'array with maximum items' => [
        ['description' => generateTranslatableArray(25), 'x' => null],
        null,
    ],

    'array above maximum items' => [
        ['description' => generateTranslatableArray(26), 'x' => null],
        fn () => __('validation.translatable.array.max', ['attribute' => 'description', 'max' => 25]),
    ],

    'translation below minimum length' => [
        ['en' => 'ab'],
        fn () => __('validation.translatable.string.min', ['attribute' => 'description', 'min' => 3]),
    ],

    'translation at minimum length' => [
        ['en' => 'abc'],
        null,
    ],

    'translation at maximum length' => [
        ['en' => str_repeat('a', 100)],
        null,
    ],

    'translation above maximum length' => [
        ['en' => str_repeat('a', 101)],
        fn () => __('validation.translatable.string.max', ['attribute' => 'description', 'max' => 100]),
    ],
];

/**
 * Base datasets for testing the 'users' field validation rules on the
 * AssignUsersToRoleController and RemoveUsersFromRoleController endpoints.
 */
$manageUsersTestData = [
    'rejects null' => [null, fn () => __('validation.required', ['attribute' => 'users'])],

    'rejects empty array' => [[], fn () => __('validation.required', ['attribute' => 'users'])],

    'rejects non-array' => ['invalid', fn () => __('validation.array', ['attribute' => 'users'])],

    'accepts up to 50 users' => [
        fn () => User::factory(50)->create()->pluck('uuid')->toArray(),
        null,
    ],

    'rejects more than 50 users' => [
        fn () => User::factory(51)->create()->pluck('uuid')->toArray(),
        fn () => __('validation.max.array', ['attribute' => 'users', 'max' => 50]),
    ],

    'accepts valid uuid' => [
        fn () => ['820fef3c-452a-3c16-9a35-c20b4bd9655d'],
        null,
    ],

    'accepts valid email' => [
        fn () => ['test@example.com'],
        null,
    ],

    'rejects invalid uuid or email' => [
        fn () => ['some-invalid-input'],
        fn () => __('validation.uuid_or_email', ['attribute' => 'users.0']),
    ],

    'rejects too long uuid or email' => [
        fn () => [str()->random(50).'@example.com'],
        fn () => __('validation.max.string', ['attribute' => 'users.0', 'max' => 50]),
    ],
];

/**
 * Dataset for testing the 'search' field validation rules on the
 * RoleController@index endpoint.
 */
dataset('RoleController@index - search field', [
    'empty' => ['', null],

    'null' => [null, null],

    'at maximum length' => [
        str_repeat('a', 100),
        null,
    ],

    'above maximum length' => [
        str_repeat('a', 101),
        fn () => __('validation.max.string', ['attribute' => 'search', 'max' => 100]),
    ],
]);

/**
 * Dataset for testing the 'displayName' field validation rules on the
 * RoleController@store endpoint.
 */
dataset('RoleController@store - displayName field', $displayNameTestData);

/**
 * Dataset for testing the 'description' field validation rules on the
 * RoleController@store endpoint.
 */
dataset('RoleController@store - description field', $descriptionTestData);

/**
 * Dataset for testing the 'scope' field validation rules on the
 * RoleController@store endpoint.
 */
dataset('RoleController@store - scope field', [
    'system' => ['system', null],

    'collection' => ['collection', null],

    'invalid' => [
        'invalid',
        fn () => __('validation.enum', ['attribute' => 'scope']),
    ],

    'null' => [
        null,
        fn () => __('validation.required', ['attribute' => 'scope']),
    ],

    'empty' => [
        '',
        fn () => __('validation.required', ['attribute' => 'scope']),
    ],
]);

/**
 * Dataset for testing the 'displayName' field validation rules on the
 * RoleController@update endpoint.
 */
dataset('RoleController@update - displayName field', $displayNameTestData);

/**
 * Dataset for testing the 'description' field validation rules on the
 * RoleController@update endpoint.
 */
dataset('RoleController@update - description field', $descriptionTestData);

/**
 * Dataset for testing the 'permissions' field validation rules on the
 * SyncRolePermissionsController.
 */
dataset('SyncRolePermissionsController - permissions field', [
    'null' => [null, fn () => __('validation.array', ['attribute' => 'permissions'])],
    'empty' => [[], null],
    'invalid' => ['invalid', fn () => __('validation.array', ['attribute' => 'permissions'])],
]);

/**
 * Dataset for testing the 'users' field validation rules on the
 * AssignUsersToRoleController.
 */
dataset('AssignUsersToRoleController - users field', $manageUsersTestData);
dataset('RemoveUsersFromRoleController - users field', $manageUsersTestData);
