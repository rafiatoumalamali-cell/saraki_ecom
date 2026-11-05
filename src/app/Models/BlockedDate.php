<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['blocked_date', 'reason'])]
class BlockedDate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['blocked_date' => 'date'];
    }
}
