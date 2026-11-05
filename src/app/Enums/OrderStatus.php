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

    /**
     * Badge tone from the design system status table (§2.4).
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed, self::InProduction => 'info',
            self::Ready => 'success',
            self::Delivered => 'success-filled',
            self::Cancelled => 'danger',
        };
    }

    public function label(): string
    {
        return __('statuses.order.'.$this->value);
    }
}
