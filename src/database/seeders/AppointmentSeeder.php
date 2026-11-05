<?php

namespace Database\Seeders;

use App\Models\AppointmentService;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name_fr' => 'Consultation', 'name_en' => 'Consultation', 'duration_min' => 30],
            ['name_fr' => 'Prise de mesures', 'name_en' => 'Measurements', 'duration_min' => 45],
            ['name_fr' => 'Essayage', 'name_en' => 'Fitting', 'duration_min' => 45],
        ];

        foreach ($services as $service) {
            AppointmentService::updateOrCreate(
                ['name_fr' => $service['name_fr']],
                $service + ['is_active' => true]
            );
        }

        // 0 = Sunday … 6 = Saturday. Open Monday to Saturday, 09:00–18:00.
        foreach (range(0, 6) as $day) {
            WorkingHour::updateOrCreate(
                ['day_of_week' => $day],
                $day === 0
                    ? ['open_time' => null, 'close_time' => null, 'is_closed' => true]
                    : ['open_time' => '09:00:00', 'close_time' => '18:00:00', 'is_closed' => false]
            );
        }
    }
}
