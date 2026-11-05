<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'service_id', 'appointment_date', 'start_time', 'end_time', 'notes', 'admin_notes'])]
class Appointment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'status' => AppointmentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(AppointmentService::class, 'service_id');
    }

    public function scopeBlocking($query)
    {
        return $query->whereIn('status', AppointmentStatus::blocking());
    }

    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('appointment_date', $date);
    }
}
