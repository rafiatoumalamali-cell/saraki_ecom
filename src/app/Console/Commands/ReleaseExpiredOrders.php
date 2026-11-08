<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

/**
 * Unpaid online orders hold stock. This is the sweep that gives that stock back
 * once the payment window has closed, so the shelf does not stay frozen forever.
 */
class ReleaseExpiredOrders extends Command
{
    protected $signature = 'orders:release-expired';

    protected $description = 'Cancel unpaid online orders past their window and put their stock back';

    public function handle(OrderService $orders): int
    {
        $released = $orders->releaseExpired();

        $this->info($released === 1
            ? 'Released 1 expired order.'
            : "Released {$released} expired orders.");

        return self::SUCCESS;
    }
}
