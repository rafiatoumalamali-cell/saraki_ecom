<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\PaymentMethods;
use App\Services\ShippingZones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['delivery_name', 'delivery_phone', 'delivery_email', 'delivery_address', 'delivery_city', 'notes']),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $zones = app(ShippingZones::class)->names();
        $methods = app(PaymentMethods::class)->keys();

        return [
            'delivery_name' => ['required', 'string', 'max:120'],
            'delivery_phone' => ['required', 'string', 'max:30'],
            'delivery_email' => ['nullable', 'email', 'max:191'],
            'delivery_address' => ['required', 'string', 'max:255'],
            // Zones come from settings: the fee is looked up from the chosen name,
            // never sent by the browser. A shop with no zones falls back to a typed city.
            'shipping_zone' => $zones === []
                ? ['nullable', 'string']
                : ['required', 'string', Rule::in($zones)],
            'delivery_city' => $zones === []
                ? ['required', 'string', 'max:120']
                : ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', Rule::in($methods)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shipping_zone.in' => __('shop.checkout.unknown_zone'),
        ];
    }
}
