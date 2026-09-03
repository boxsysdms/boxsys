<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands(app()->isProduction());

        Model::unguard();
        Model::shouldBeStrict(app()->isLocal());

        Date::use(CarbonImmutable::class);

        $this->registerQueryMacros();
    }

    /**
     * Register custom query builder macros.
     *
     * Ignored by test coverage because test environments always use SQLite,
     * so the PostgreSQL branch condition is always false.
     *
     * @codeCoverageIgnore
     */
    public function registerQueryMacros(): void
    {
        $macro = function (string $column, string $value): QueryBuilder|EloquentBuilder {
            if (DB::getDriverName() === 'pgsql') {
                /**
                 * @var EloquentBuilder<Model>
                 *
                 * @phpstan-ignore method.notFound
                 */
                return $this->where($column, 'ILIKE', "%{$value}%");
            }

            /**
             * @var QueryBuilder
             *
             * @phpstan-ignore method.notFound
             */
            return $this->whereRaw("LOWER($column) LIKE LOWER(?)", ["%{$value}%"]);
        };

        QueryBuilder::macro('whereLikeInsensitive', $macro);
        EloquentBuilder::macro('whereLikeInsensitive', $macro);
    }
}
