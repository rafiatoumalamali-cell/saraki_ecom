<?php

namespace Database\Factories;

use App\Enums\FitPreference;
use App\Models\CustomerMeasurement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerMeasurement>
 */
class CustomerMeasurementFactory extends Factory
{
    protected $model = CustomerMeasurement::class;

    /**
     * A realistic adult body rather than random values inside each range, so the
     * seeded figures can be read out in a fitting without looking absurd.
     */
    private const TYPICAL_CM = [
        'height_cm' => 168, 'chest_cm' => 92, 'underbust_cm' => 78, 'waist_cm' => 74,
        'hips_cm' => 98, 'shoulder_cm' => 42, 'back_width_cm' => 38, 'neck_cm' => 38,
        'arm_length_cm' => 58, 'sleeve_length_cm' => 60, 'bicep_cm' => 30,
        'inseam_cm' => 78, 'thigh_cm' => 56, 'knee_cm' => 40, 'calf_cm' => 38,
        'dress_length_cm' => 110,
    ];

    public function definition(): array
    {
        $fields = [];

        foreach (self::TYPICAL_CM as $field => $cm) {
            $fields[$field] = fake()->randomFloat(2, $cm - 2, $cm + 2);
        }

        return $fields + [
            'user_id' => User::factory(),
            'fit_preference' => fake()->randomElement(FitPreference::cases()),
            'taken_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'notes' => null,
        ];
    }

    /**
     * A customer who started the sheet but never finished it.
     */
    public function partial(): static
    {
        return $this->state(fn () => [
            'chest_cm' => null,
            'underbust_cm' => null,
            'arm_length_cm' => null,
            'sleeve_length_cm' => null,
            'bicep_cm' => null,
            'knee_cm' => null,
            'calf_cm' => null,
            'dress_length_cm' => null,
            'fit_preference' => null,
            'taken_at' => null,
        ]);
    }
}
