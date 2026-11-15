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
            ['group' => 'general', 'key' => 'contact_email', 'value' => config('saraki.admin_email')],
            ['group' => 'general', 'key' => 'contact_phone', 'value' => null],
            ['group' => 'general', 'key' => 'store_address', 'value' => null],
            ['group' => 'general', 'key' => 'whatsapp_number', 'value' => null],
            ['group' => 'general', 'key' => 'map_embed', 'value' => null],

            ['group' => 'commerce', 'key' => 'shipping_zones', 'value' => [
                ['name' => 'Niamey', 'name_en' => 'Niamey', 'fee' => 30, 'delivery_days' => 2],
                ['name' => 'Capitales régionales', 'name_en' => 'Regional capitals', 'fee' => 60, 'delivery_days' => 4],
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
