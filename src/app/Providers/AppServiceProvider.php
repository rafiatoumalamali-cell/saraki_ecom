<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Admins pass every policy; non-admins fall through to the policy itself.
        Gate::before(fn (?User $user) => $user?->isAdmin() ? true : null);
    }
}
