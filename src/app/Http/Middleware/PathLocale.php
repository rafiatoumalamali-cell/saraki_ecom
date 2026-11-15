<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global middleware so it also runs for URLs that match no route: a mistyped
 * /en/... address still gets the English error page instead of the default
 * French one. SetLocale re-resolves the locale properly on matched routes.
 */
class PathLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $segment = $request->segment(1);

        if (is_string($segment) && in_array($segment, config('saraki.supported_locales'), true)) {
            app()->setLocale($segment);
        }

        return $next($request);
    }
}
