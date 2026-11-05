<?php

namespace App\Models;

use App\Enums\FitPreference;
use Database\Factories\CustomerMeasurementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'height_cm', 'chest_cm', 'underbust_cm', 'waist_cm', 'hips_cm', 'shoulder_cm',
    'back_width_cm', 'neck_cm', 'arm_length_cm', 'sleeve_length_cm', 'bicep_cm',
    'inseam_cm', 'thigh_cm', 'knee_cm', 'calf_cm', 'dress_length_cm',
    'fit_preference', 'taken_at', 'notes',
])]
class CustomerMeasurement extends Model
{
    /** @use HasFactory<CustomerMeasurementFactory> */
    use HasFactory;

    /**
     * The single source of truth for the tailor's measurement sheet: every body
     * field, the form group it belongs to, and the centimetre range a real
     * customer can enter. Column order here matches the migration.
     *
     * @var array<string, array{group: string, min: int, max: int}>
     */
    public const BODY_FIELDS = [
        'height_cm' => ['group' => 'stature', 'min' => 80, 'max' => 250],
        'chest_cm' => ['group' => 'upper', 'min' => 40, 'max' => 200],
        'underbust_cm' => ['group' => 'upper', 'min' => 35, 'max' => 200],
        'waist_cm' => ['group' => 'upper', 'min' => 35, 'max' => 200],
        'hips_cm' => ['group' => 'upper', 'min' => 40, 'max' => 220],
        'shoulder_cm' => ['group' => 'upper', 'min' => 20, 'max' => 80],
        'back_width_cm' => ['group' => 'upper', 'min' => 20, 'max' => 90],
        'neck_cm' => ['group' => 'upper', 'min' => 20, 'max' => 70],
        'arm_length_cm' => ['group' => 'upper', 'min' => 20, 'max' => 100],
        'sleeve_length_cm' => ['group' => 'upper', 'min' => 10, 'max' => 100],
        'bicep_cm' => ['group' => 'upper', 'min' => 12, 'max' => 80],
        'inseam_cm' => ['group' => 'lower', 'min' => 30, 'max' => 130],
        'thigh_cm' => ['group' => 'lower', 'min' => 20, 'max' => 120],
        'knee_cm' => ['group' => 'lower', 'min' => 15, 'max' => 90],
        'calf_cm' => ['group' => 'lower', 'min' => 12, 'max' => 90],
        'dress_length_cm' => ['group' => 'length', 'min' => 20, 'max' => 200],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        $measurements = [];

        foreach (array_keys(self::BODY_FIELDS) as $field) {
            $measurements[$field] = 'float';
        }

        return $measurements + [
            'fit_preference' => FitPreference::class,
            'taken_at' => 'date',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::BODY_FIELDS as $field => $meta) {
            $groups[$meta['group']][] = $field;
        }

        return $groups;
    }

    public static function label(string $field): string
    {
        return __('measurements.labels.'.$field);
    }

    public function display(string $field): ?string
    {
        return centimetres($this->getAttribute($field));
    }

    public function recordedCount(): int
    {
        return collect(array_keys(self::BODY_FIELDS))
            ->filter(fn (string $field) => filled($this->getAttribute($field)))
            ->count();
    }
}
