<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['day_of_week', 'open_time', 'close_time', 'is_closed'])]
class WorkingHour extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'open_time' => 'datetime:H:i',
            'close_time' => 'datetime:H:i',
            'is_closed' => 'boolean',
        ];
    }

    public function isOpenDay(): bool
    {
        return ! $this->is_closed;
    }
}
