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
}
