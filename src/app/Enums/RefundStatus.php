<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
}
