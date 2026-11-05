<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name_fr', 'name_en', 'duration_min', 'is_active'])]
class AppointmentService extends Model
{
    use HasFactory, HasTranslations;

    protected function casts(): array
    {
        return [
            'duration_min' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
