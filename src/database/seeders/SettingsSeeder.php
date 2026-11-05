<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['group' => 'general', 'key' => 'store_name', 'value' => config('saraki.store_name')],
            ['group' => 'general', 'key' => 'currency', 'value' => config('saraki.currency')],
            ['group' => 'general', 'key' => 'default_locale', 'value' => config('app.locale')],
            ['group' => 'general', 'key' => 'whatsapp_number', 'value' => null],
            ['group' => 'general', 'key' => 'map_embed', 'value' => null],

            ['group' => 'commerce', 'key' => 'shipping_zones', 'value' => [
                ['name' => 'Grand Accra', 'name_en' => 'Greater Accra', 'fee' => 30, 'delivery_days' => 2],
                ['name' => 'Tema', 'name_en' => 'Tema', 'fee' => 45, 'delivery_days' => 3],
                ['name' => 'Kumasi', 'name_en' => 'Kumasi', 'fee' => 60, 'delivery_days' => 4],
                ['name' => 'Autre ville', 'name_en' => 'Other city', 'fee' => 80, 'delivery_days' => 6],
            ]],
            ['group' => 'commerce', 'key' => 'payment_methods', 'value' => [
                'paystack' => true,
                'cod' => false,
                'transfer' => false,
            ]],
            ['group' => 'commerce', 'key' => 'tax_display', 'value' => false],
            ['group' => 'commerce', 'key' => 'order_expiry_minutes', 'value' => config('saraki.order_expiry_minutes')],

            ['group' => 'social', 'key' => 'social_links', 'value' => [
                'instagram' => null,
                'facebook' => null,
                'tiktok' => null,
            ]],
        ];

        foreach ($rows as $row) {
            Setting::updateOrCreate(['key' => $row['key']], [
                'group' => $row['group'],
                'value' => $row['value'],
            ]);
        }
    }
}
