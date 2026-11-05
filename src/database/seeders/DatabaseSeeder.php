<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedAdmin();

        $this->call([
            SettingsSeeder::class,
            CatalogueSeeder::class,
            AppointmentSeeder::class,
            CustomerSeeder::class,
        ]);
    }

    /**
     * `role` is deliberately not mass-assignable, so the admin is written explicitly.
     */
    private function seedAdmin(): void
    {
        $password = env('SEED_ADMIN_PASSWORD');

        if ($password === null && app()->environment('production')) {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD before seeding in production.');
        }

        $admin = User::firstOrNew(['email' => config('saraki.admin_email')]);

        $admin->forceFill([
            'name' => config('saraki.store_name').' Admin',
            'password' => Hash::make($password ?? 'password'),
            'locale' => config('app.locale'),
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();
    }
}
