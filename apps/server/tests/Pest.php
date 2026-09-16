<?php

declare(strict_types=1);

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit', 'Feature');

/**
 * Generates a closure that returns an array of fake translations for testing.
 *
 * Each translation is assigned a unique language code and a fake sentence.
 * When enabled, the generated locales are also added to the configured
 * list of supported locales.
 *
 * @param  int  $items  Number of translations to generate.
 * @param  bool  $appendLocalesToSupported  Whether to add the generated locales
 *                                          to the supported locales configuration.
 * @return Closure(): array<string, string>
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
 * Provides dataset cases for testing the pagination 'page' parameter,
 * including invalid and valid values.
 *
 * @return array<string, array{mixed, ?string}>
 */
function getPaginationPageDataset(): array
{
    return [
        'integer' => [
            'abc',
            fn () => __('validation.integer', ['attribute' => 'page']),
        ],
        'minimum' => [
            0,
            fn () => __('validation.min.numeric', ['attribute' => 'page', 'min' => 1]),
        ],
        'valid' => [1, null],
    ];
}

/**
 * Provides dataset cases for testing the pagination 'perPage' parameter,
 * including invalid and valid values.
 *
 * @param  int  $minPerPage  Minimum number of items allowed per page.
 * @param  int  $maxPerPage  Maximum number of items allowed per page.
 * @return array<string, array{mixed, ?string}>
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
 * Provides dataset cases for testing the pagination 'sortOrder' parameter,
 * including valid and invalid values.
 *
 * @return array<string, array{string, ?string}>
 */
function getPaginationSortOrderDataset(): array
{
    return [
        'ascending' => ['asc', null],
        'descending' => ['desc', null],
        'invalid' => [
            'invalid',
            fn () => __('validation.in', ['attribute' => 'sort order']),
        ],
    ];
}
