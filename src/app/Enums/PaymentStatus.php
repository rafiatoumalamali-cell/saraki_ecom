<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Initialized = 'initialized';
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case Abandoned = 'abandoned';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    /**
     * Badge tone from the design system status table (§2.4).
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::Success => 'success-filled',
            self::Initialized, self::Pending => 'warning',
            self::Failed, self::Abandoned => 'danger',
            self::Refunded, self::PartiallyRefunded => 'info',
        };
    }

    public function label(): string
    {
        return __('statuses.payment_state.'.$this->value);
    }
}
