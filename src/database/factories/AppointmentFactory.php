<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'service_id' => AppointmentServiceFactory::new(),
            'appointment_date' => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'start_time' => fake()->randomElement(['09:00:00', '10:30:00', '14:00:00', '16:00:00']),
            'end_time' => '18:00:00',
            'status' => AppointmentStatus::Pending,
            'notes' => fake()->optional()->sentence(8),
            'admin_notes' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Appointment $appointment) {
            $start = $appointment->start_time;
            $end = $start->copy()->addMinutes($appointment->service->duration_min);

            $appointment->end_time = $end->format('H:i:s');
        });
    }

    public function pending(): static
    {
        return $this->withStatus(AppointmentStatus::Pending);
    }

    public function approved(): static
    {
        return $this->withStatus(AppointmentStatus::Approved);
    }

    public function rescheduled(): static
    {
        return $this->withStatus(AppointmentStatus::Rescheduled);
    }

    public function cancelled(): static
    {
        return $this->withStatus(AppointmentStatus::Cancelled);
    }

    public function completed(): static
    {
        return $this->withStatus(AppointmentStatus::Completed);
    }

    /**
     * The status column is guarded, so the state is forced on the model itself
     * rather than handed to the constructor.
     */
    public function withStatus(AppointmentStatus $status): static
    {
        return $this
            ->afterMaking(fn (Appointment $appointment) => $appointment->forceFill(['status' => $status]))
            ->afterCreating(fn (Appointment $appointment) => $appointment->forceFill(['status' => $status])->save());
    }
}
