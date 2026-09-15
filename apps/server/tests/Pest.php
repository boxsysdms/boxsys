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
 * Returns a closure that generates a fake localized dataset and optionally
 * registers the generated locales into the runtime configuration.
 *
 * The returned closure generates an associative array where keys are unique
 * ISO language codes and values are fake sentences. When the parameter
 * `$appendLocalesToSupported` is true, the closure merges the newly
 * generated locale codes into the `boxsys.locales.supported` array.
 *
 * @param  int  $items  The number of unique localized elements to generate.
 * @param  bool  $appendLocalesToSupported  Whether to dynamically append the
 *                                          generated locale keys to the
 *                                          supported locales configuration.
 * @return Closure(): array<string, string> A factory closure that returns
 *                                          the array of translations.
 */
function generateTranslatableArray(int $items, bool $appendLocalesToSupported = true)
{
    return function () use ($items, $appendLocalesToSupported) {
        $translatableArray = [];

        for ($i = 0; $i < $items; $i++) {
            $translatableArray[fake()->unique()->languageCode()] = fake()->sentence();
        }

        if ($appendLocalesToSupported) {
            $supportedLocales = config('boxsys.locales.supported', []);

            config([
                'boxsys.locales.supported' => array_merge(
                    $supportedLocales,
                    array_keys($translatableArray)
                ),
            ]);
        }

        return $translatableArray;
    };
}

/**
 * Provides a structured dataset for testing pagination 'page' validation
 * rules.
 *
 * Returns a matrix of test cases simulating various input scenarios for a
 * pagination page parameter. Each dataset element maps a scenario key to
 * an array containing the payload value and an expected validation error
 * message closure (or null for valid inputs).
 *
 * @return array<string, array{0: mixed, 1: (Closure(): string)|null}>
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
 * Provides a structured dataset for testing 'per page' validation rules.
 *
 * Generates a matrix of boundary and invalid test cases based on defined
 * minimum and maximum limits. Each item maps a specific scenario to an
 * array containing the test payload and an expected error message closure
 * (or null for the valid scenario).
 *
 * @param  int  $minPerPage  The lowest allowed items per page threshold.
 * @param  int  $maxPerPage  The highest allowed items per page threshold.
 * @return array<string, array{0: mixed, 1: (Closure(): string)|null}>
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
 * Provides a structured dataset for testing sort order validation rules.
 *
 * Returns a matrix of test cases for direction parameters (e.g., sorting).
 * Each scenario maps to an array containing the input direction string and
 * an expected validation error message closure, which evaluates to null for
 * supported sorting directions ('asc' and 'desc').
 *
 * @return array<string, array{0: string, 1: (Closure(): string)|null}>
 */
function getPaginationSortOrderDataset(): array
{
    return [
        'ascending' => ['asc', null],
        'descending' => ['desc', null],
        'invalid' => ['invalid', fn () => __('validation.in', ['attribute' => 'sort order'])],
    ];
}
