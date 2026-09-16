<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\User;
use Closure;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\json;

/**
 * Helper function to test authentication and authorization.
 *
 * @param  string  $method  The HTTP method (GET, POST, PUT, PATCH, DELETE).
 * @param  string  $route  The route name.
 * @param  ?Closure  $routeParameters  A closure that returns an array of routeParameters for the request.
 * @param  bool  $withAuthorization  Whether to test authorization for authenticated users.
 */
function testAuthenticationAndAuthorization(string $method, string $route, ?Closure $routeParameters = null, bool $withAuthorization = true): void
{
    it('returns 401 for unauthenticated users', function () use ($method, $route, $routeParameters) {
        $routeParameters = $routeParameters ? $routeParameters() : [];
        $response = $method === 'GET'
            ? json('GET', route($route, $routeParameters))
            : json($method, route($route, $routeParameters));

        expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED);
    });

    if ($withAuthorization) {
        it('returns 403 for users without permission', function () use ($method, $route, $routeParameters) {
            $routeParameters = $routeParameters ? $routeParameters() : [];
            $userWithoutPermission = User::factory()->create();

            $response = actingAs($userWithoutPermission)
                ->json($method, route($route, $routeParameters));

            expect($response->status())->toBe(Response::HTTP_FORBIDDEN);
        });
    }
}

/**
 * Helper function to test form request validations.
 *
 * @param  string  $method  The HTTP method (GET, POST, PUT, PATCH, DELETE).
 * @param  string  $route  The route name.
 * @param  array<string, string|Closure>  $fieldsWithDatasets  An associative array where keys are field names and values are dataset names.
 * @param  ?Closure  $routeParameters  A closure that returns an array of parameters for the route.
 */
function testFormRequestValidations(
    string $method,
    string $route,
    array $fieldsWithDatasets,
    ?Closure $routeParameters = null,
    array|Closure|null $alwaysIncludedData = null
): void {
    foreach ($fieldsWithDatasets as $field => $dataset) {
        it(
            "validates {$field} field",
            function (mixed $value, ?string $expectedError = null) use ($method, $route, $field, $routeParameters, $alwaysIncludedData) {
                $user = test()->user;

                // In Pest all datasets are evaluated before running the test,
                // so we need to use a closure to delay the evaluation of route parameters.
                $routeParameters = $routeParameters ? $routeParameters() : [];

                // Prepare request parameters. If $value is an array with
                // multiple entries, use it as is. Useful for testing complex
                // validation scenarios where field dependencies exist.
                $parameters = is_array($value) && count($value) > 1
                    ? $value
                    : [$field => $value];

                // Evaluate any closures in the parameters to get their actual values.
                $parameters = array_map(fn ($value) => $value instanceof Closure ? $value() : $value, $parameters);

                // Evaluate any closures in the always included data to get their
                // actual values. If it is null, default to an empty array.
                $alwaysIncludedData = $alwaysIncludedData instanceof Closure
                    ? $alwaysIncludedData()
                    : ($alwaysIncludedData ?? []);

                $parameters = array_merge($alwaysIncludedData, $parameters);

                // In GET requests, parameters are sent as query parameters. In
                // other requests, they are sent as JSON body, but we still need
                // to send model binding parameters (like resource UUIDs) as part of the route.
                $response = $method === 'GET'
                    ? actingAs($user)->json('GET', route($route, [...$routeParameters, ...$parameters]))
                    : actingAs($user)->json($method, route($route, $routeParameters), $parameters);

                if ($response->status() === Response::HTTP_NO_CONTENT) {
                    expect(true)->toBe(true);   // No content response, test passes automatically.

                    return;
                }

                $errors = $response->json('errors');

                $error = $errors["{$field}.0"][0]
                    ?? $errors[$field][0]
                    ?? null;

                is_null($expectedError)
                    ? expect($error)->toBeNull()
                    : expect($error)->toBe($expectedError);
            }
        )->with(
            $dataset instanceof Closure ? $dataset() : $dataset
        );
    }
}

/**
 * Helper function to test pagination parameters.
 *
 * @param  string  $route  The route name.
 * @param  ?Closure  $routeParameters  A closure that returns an array of parameters for the route.
 */
function testPaginationParameters(
    string $route,
    ?Closure $routeParameters = null,
    int $minPerPage = 15,
    int $maxPerPage = 50,
    bool $hasSort = true
): void {
    $dataset = [
        'page' => fn () => getPaginationPageDataset(),
        'perPage' => fn () => getPaginationPerPageDataset($minPerPage, $maxPerPage),
    ];

    if ($hasSort) {
        $dataset['sortOrder'] = fn () => getPaginationSortOrderDataset();
    }

    testFormRequestValidations('GET', $route, $dataset, $routeParameters);
}
