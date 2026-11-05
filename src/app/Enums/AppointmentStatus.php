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
}
