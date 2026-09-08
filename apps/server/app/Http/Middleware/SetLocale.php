<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $locale = mb_strtolower(str_replace('_', '-', (string) $locale));
        if ($locale === '') {
            $locale = (string) config('app.locale');     // @codeCoverageIgnore
        } else {
            // Normalize regional variants like "pt-PT" -> "pt" to match app locales.
            $locale = explode('-', $locale, 2)[0];
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        return $next($request);
    }
}
