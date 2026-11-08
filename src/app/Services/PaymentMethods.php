<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

/**
 * Which payment options the shop is allowed to offer right now. Paystack also
 * needs real keys: without them the option is hidden rather than shown and broken.
 */
class PaymentMethods
{
    public const PAYSTACK = 'paystack';

    public const COD = 'cod';

    public const TRANSFER = 'transfer';

    public function __construct(private readonly PaystackGateway $gateway) {}

    /**
     * @return array<string, bool>
     */
    public function enabled(): array
    {
        $configured = Setting::query()->where('key', 'payment_methods')->first()?->value;

        $flags = is_array($configured) ? $configured : [self::PAYSTACK => true];

        return [
            self::PAYSTACK => (bool) ($flags[self::PAYSTACK] ?? false) && $this->gateway->isConfigured(),
            self::COD => (bool) ($flags[self::COD] ?? false),
            self::TRANSFER => (bool) ($flags[self::TRANSFER] ?? false),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys(array_filter($this->enabled()));
    }

    /**
     * @return array<string, array{label: string, hint: string}>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->keys() as $key) {
            $options[$key] = [
                'label' => __('shop.checkout.method_'.$key),
                'hint' => __('shop.checkout.method_hint_'.$key),
            ];
        }

        return $options;
    }
}
