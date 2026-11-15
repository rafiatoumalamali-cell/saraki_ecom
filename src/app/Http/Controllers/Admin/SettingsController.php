<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Services\PaymentMethods;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly Settings $settings,
        private readonly PaymentMethods $paymentMethods,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'paystackUnconfigured' => $this->paymentMethods->paystackRequestedButUnconfigured(),
        ]);
    }

    /**
     * Every field on the form is written on every save, so a cleared box really
     * clears the setting instead of leaving the previous value behind. The map
     * address is normalised here: what the form accepts is exactly what the
     * iframe will render.
     */
    public function update(SettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->settings->putMany([
            'store_name' => $data['store_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'store_address' => $data['store_address'],
            'whatsapp_number' => $data['whatsapp_number'],
            'map_embed' => $data['map_embed'] === null ? null : Settings::embedUrl($data['map_embed']),
        ], 'general');

        $this->settings->putMany([
            'shipping_zones' => collect($data['shipping_zones'])
                ->map(fn (array $zone) => [
                    'name' => $zone['name'],
                    'name_en' => $zone['name_en'] ?: $zone['name'],
                    'fee' => (float) $zone['fee'],
                    'delivery_days' => (int) $zone['delivery_days'],
                ])
                ->values()
                ->all(),
            'payment_methods' => [
                'paystack' => (bool) ($data['payment_methods']['paystack'] ?? false),
                'cod' => (bool) ($data['payment_methods']['cod'] ?? false),
                'transfer' => (bool) ($data['payment_methods']['transfer'] ?? false),
            ],
            'tax_display' => (bool) $data['tax_display'],
            'order_expiry_minutes' => (int) $data['order_expiry_minutes'],
        ], 'commerce');

        $this->settings->putMany([
            'social_links' => collect($data['social_links'] ?? [])
                ->map(fn (?string $url) => filled($url) ? $url : null)
                ->all(),
        ], 'social');

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', __('admin.settings.saved'));
    }
}
