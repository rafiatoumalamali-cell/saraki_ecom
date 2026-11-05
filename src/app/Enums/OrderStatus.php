<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProduction = 'in_production';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function isFinal(): bool
    {
        return $this === self::Delivered || $this === self::Cancelled;
    }
}
