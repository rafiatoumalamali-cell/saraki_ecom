<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

/**
 * A visitor can fill a basket before signing in; this hands it to the account the
 * moment the session is authenticated. It runs on the Login event, while the guest
 * session id is still the one the cart was stored under.
 */
class MergeGuestCart
{
    public function __construct(private readonly CartService $carts) {}

    public function handle(Login $event): void
    {
        $this->carts->mergeGuestCart($event->user);
    }
}
