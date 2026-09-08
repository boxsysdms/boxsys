<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Get the dataset for pagination page validation.
 */
function getPaginationPageDataset(): array
{
    return [
        'integer' => ['abc', fn () => __('validation.integer', ['attribute' => 'page'])],
        'minimum' => [0, fn () => __('validation.min.numeric', ['attribute' => 'page', 'min' => 1])],
        'valid' => [1, null],
    ];
}

/**
 * Get the dataset for pagination per page validation.
 */
function getPaginationPerPageDataset(int $minPerPage = 15, int $maxPerPage = 50): array
{
    return [
        'integer' => [
            'abc',
            fn () => __('validation.integer', ['attribute' => 'per page']),
        ],
        'minimum' => [
            $minPerPage - 1,
            fn () => __('validation.min.numeric', ['attribute' => 'per page', 'min' => $minPerPage]),
        ],
        'maximum' => [
            $maxPerPage + 1,
            fn () => __('validation.max.numeric', ['attribute' => 'per page', 'max' => $maxPerPage]),
        ],
        'valid' => [(int) (($minPerPage + $maxPerPage) / 2), null],
    ];
}

/**
 * Get the dataset for pagination sort order validation.
 */
function getPaginationSortOrderDataset(): array
{
    return [
        'ascending' => ['asc', null],
        'descending' => ['desc', null],
        'invalid' => ['invalid', fn () => __('validation.in', ['attribute' => 'sort order'])],
    ];
}
