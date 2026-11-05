<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Route prefix wins, then the session, then the account preference.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('saraki.supported_locales');

        $locale = collect([
            $request->route('locale'),
            $request->session()->get('locale'),
            $request->user()?->locale,
        ])->first(fn ($candidate) => in_array($candidate, $supported, true));

        $locale ??= config('app.locale');

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
