<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rescheduled = 'rescheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    /** @return list<self> */
    public static function blocking(): array
    {
        return [self::Pending, self::Approved, self::Rescheduled];
    }

    /**
     * Badge tone from the design system status table (§2.4).
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved, self::Rescheduled => 'info',
            self::Cancelled => 'danger',
            self::Completed => 'success-filled',
        };
    }

    public function label(): string
    {
        return __('statuses.appointment.'.$this->value);
    }

    /** A blocking booking still holds its slot against new requests. */
    public function isBlocking(): bool
    {
        return in_array($this, self::blocking(), true);
    }

    public function isFinal(): bool
    {
        return $this === self::Cancelled || $this === self::Completed;
    }
}
