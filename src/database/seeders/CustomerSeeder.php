<?php

namespace Database\Seeders;

use App\Enums\FitPreference;
use App\Models\CustomerMeasurement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo customers so the tailor's measurement sheet has something to show.
 * Emails are the key, so re-seeding never duplicates an account.
 */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->customers() as $email => $data) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'locale' => 'fr',
                ]
            );

            // Not mass-assignable, and needed so a demo customer can reach /profile.
            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

            if ($data['measurements'] === null) {
                $user->measurements()->delete();

                continue;
            }

            CustomerMeasurement::updateOrCreate(
                ['user_id' => $user->id],
                $data['measurements']
            );
        }
    }

    private function customers(): array
    {
        return [
            'ama.serwaa@example.test' => [
                'name' => 'Ama Serwaa',
                'phone' => '+233 24 555 0112',
                'measurements' => [
                    'height_cm' => 165.5, 'chest_cm' => 94, 'underbust_cm' => 79, 'waist_cm' => 76,
                    'hips_cm' => 101, 'shoulder_cm' => 41, 'back_width_cm' => 37, 'neck_cm' => 37,
                    'arm_length_cm' => 57, 'sleeve_length_cm' => 59, 'bicep_cm' => 31,
                    'inseam_cm' => 76, 'thigh_cm' => 58, 'knee_cm' => 41, 'calf_cm' => 39,
                    'dress_length_cm' => 118,
                    'fit_preference' => FitPreference::Regular->value,
                    'taken_at' => '2026-09-14',
                    'notes' => 'Préfère les manches trois-quarts. Taille haute pour les boubous.',
                ],
            ],
            'kwame.boateng@example.test' => [
                'name' => 'Kwame Boateng',
                'phone' => '+233 20 555 0174',
                'measurements' => [
                    'height_cm' => 178, 'chest_cm' => 104, 'underbust_cm' => 92, 'waist_cm' => 88,
                    'hips_cm' => 103, 'shoulder_cm' => 46, 'back_width_cm' => 42, 'neck_cm' => 41,
                    'arm_length_cm' => 63, 'sleeve_length_cm' => 65, 'bicep_cm' => 36,
                    'inseam_cm' => 84, 'thigh_cm' => 62, 'knee_cm' => 44, 'calf_cm' => 41,
                    'dress_length_cm' => null,
                    'fit_preference' => FitPreference::Slim->value,
                    'taken_at' => '2026-10-02',
                    'notes' => null,
                ],
            ],
            'fatima.iddrisu@example.test' => [
                'name' => 'Fatima Iddrisu',
                'phone' => '+233 27 555 0139',
                'measurements' => [
                    'height_cm' => 159, 'chest_cm' => 88, 'underbust_cm' => 74, 'waist_cm' => 71,
                    'hips_cm' => 96, 'shoulder_cm' => 39, 'back_width_cm' => 36, 'neck_cm' => 35,
                    'arm_length_cm' => null, 'sleeve_length_cm' => null, 'bicep_cm' => null,
                    'inseam_cm' => 71, 'thigh_cm' => 55, 'knee_cm' => null, 'calf_cm' => null,
                    'dress_length_cm' => 104,
                    'fit_preference' => null,
                    'taken_at' => '2026-08-21',
                    'notes' => 'Fiche incomplète : manches à prendre au prochain rendez-vous.',
                ],
            ],
            'yaw.adjei@example.test' => [
                'name' => 'Yaw Adjei',
                'phone' => '+233 54 555 0186',
                'measurements' => null,
            ],
        ];
    }
}
