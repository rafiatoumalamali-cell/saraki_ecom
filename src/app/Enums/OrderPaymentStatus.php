<?php

namespace App\Enums;

enum OrderPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    /**
     * Badge tone from the design system status table (§2.4).
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::Paid => 'success-filled',
            self::Pending => 'warning',
            self::Failed, self::Refunded, self::PartiallyRefunded => 'danger',
            self::Unpaid => 'neutral',
        };
    }

    public function label(): string
    {
        return __('statuses.payment.'.$this->value);
    }
}
