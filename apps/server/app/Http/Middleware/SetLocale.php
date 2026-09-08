<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Log\log;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if (empty($locale)) {
            $acceptedLanguages = $request->getLanguages();
            $locale = $acceptedLanguages[0] ?? null;
        }

        if (empty($locale)) {
            $locale = config('app.locale');     // @codeCoverageIgnore
        }

        $locale = str_replace('_', '-', (string) $locale);

        log()->info('Setting locale', ['locale' => $locale]);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
