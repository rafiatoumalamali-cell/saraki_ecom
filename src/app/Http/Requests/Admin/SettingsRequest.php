<?php

namespace App\Http\Requests\Admin;

use App\Services\Settings;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * Reached only through the `auth` + `admin` middleware group.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shipping rows are repeatable, so an admin who adds four rows and fills two
     * must not be blocked by the two empty ones. Blank rows are dropped before
     * validation, and the toggles arrive as strings from hidden inputs.
     */
    protected function prepareForValidation(): void
    {
        $zones = collect($this->input('shipping_zones', []))
            ->filter(fn ($zone) => is_array($zone) && (filled($zone['name'] ?? null) || filled($zone['fee'] ?? null)))
            ->map(fn (array $zone) => [
                'name' => trim((string) ($zone['name'] ?? '')),
                'name_en' => trim((string) ($zone['name_en'] ?? '')),
                'fee' => $zone['fee'] ?? 0,
                'delivery_days' => $zone['delivery_days'] ?? 0,
            ])
            ->values()
            ->all();

        $methods = collect($this->input('payment_methods', []))
            ->mapWithKeys(fn ($flag, string $method) => [$method => filter_var($flag, FILTER_VALIDATE_BOOLEAN)])
            ->all();

        $social = collect($this->input('social_links', []))
            ->map(fn ($url) => trim((string) $url))
            ->all();

        $this->merge([
            'store_name' => trim((string) $this->input('store_name')),
            'contact_email' => trim((string) $this->input('contact_email')),
            'contact_phone' => trim((string) $this->input('contact_phone')) ?: null,
            'store_address' => trim((string) $this->input('store_address')) ?: null,
            'whatsapp_number' => trim((string) $this->input('whatsapp_number')) ?: null,
            'map_embed' => trim((string) $this->input('map_embed')) ?: null,
            'shipping_zones' => $zones,
            'payment_methods' => $methods,
            'tax_display' => $this->boolean('tax_display'),
            'social_links' => $social,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:80'],
            'contact_email' => ['required', 'string', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'store_address' => ['nullable', 'string', 'max:180'],
            'whatsapp_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\s.\-]+$/'],
            'map_embed' => ['nullable', 'string', 'max:2000', $this->mapRule()],

            'shipping_zones' => ['required', 'array', 'min:1', 'max:12'],
            'shipping_zones.*.name' => ['required', 'string', 'max:80'],
            'shipping_zones.*.name_en' => ['nullable', 'string', 'max:80'],
            'shipping_zones.*.fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'shipping_zones.*.delivery_days' => ['required', 'integer', 'min:0', 'max:60'],

            'payment_methods' => ['required', 'array', $this->paymentMethodRule()],
            'payment_methods.paystack' => ['boolean'],
            'payment_methods.cod' => ['boolean'],
            'payment_methods.transfer' => ['boolean'],

            'tax_display' => ['boolean'],
            'order_expiry_minutes' => ['required', 'integer', 'min:5', 'max:1440'],

            'social_links' => ['array'],
            'social_links.instagram' => ['nullable', 'string', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'string', 'url', 'max:255'],
            'social_links.tiktok' => ['nullable', 'string', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp_number.regex' => __('admin.settings.whatsapp_invalid'),
        ];
    }

    /**
     * The value ends up in an iframe src, so only a plain http(s) address is
     * accepted — Google's share snippet is unwrapped to its src first.
     */
    private function mapRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (blank($value)) {
                return;
            }

            if (Settings::embedUrl((string) $value) === null) {
                $fail(__('admin.settings.map_invalid'));
            }
        };
    }

    /**
     * Checkout with every method switched off is a dead end, so the form refuses
     * to save that state.
     */
    private function paymentMethodRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! collect($value)->contains(fn ($enabled) => (bool) $enabled)) {
                $fail(__('admin.settings.payment_required'));
            }
        };
    }
}
