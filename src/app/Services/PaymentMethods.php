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
        $flags = $this->flags();

        return [
            self::PAYSTACK => (bool) ($flags[self::PAYSTACK] ?? false) && $this->gateway->isConfigured(),
            self::COD => (bool) ($flags[self::COD] ?? false),
            self::TRANSFER => (bool) ($flags[self::TRANSFER] ?? false),
        ];
    }

    /**
     * The admin checkbox is on but the gateway has no secret key, so the method
     * is silently hidden at checkout. Admin screens surface this instead.
     */
    public function paystackRequestedButUnconfigured(): bool
    {
        return (bool) ($this->flags()[self::PAYSTACK] ?? false) && ! $this->gateway->isConfigured();
    }

    /**
     * @return array<string, bool>
     */
    private function flags(): array
    {
        $configured = Setting::query()->where('key', 'payment_methods')->first()?->value;

        return is_array($configured) ? $configured : [self::PAYSTACK => true];
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
