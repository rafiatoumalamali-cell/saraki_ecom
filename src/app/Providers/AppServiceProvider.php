<?php

namespace App\Providers;

use App\Listeners\MergeGuestCart;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One snapshot of the settings table per request, shared by every reader.
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        // Admins pass every policy; non-admins fall through to the policy itself.
        Gate::before(fn (?User $user) => $user?->isAdmin() ? true : null);

        // A basket filled before signing in follows the visitor into their account.
        Event::listen(Login::class, MergeGuestCart::class);

        // WhatsApp button, footer and contact page all read the same instance.
        View::share('settings', $this->app->make(Settings::class));

        // The contact form is the one public write on the site: five messages per
        // minute per address is generous for a person and useless for a script.
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
