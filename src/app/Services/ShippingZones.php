<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Delivery zones are edited in settings, not in code, so the shop can add a city
 * without a deployment. The fee always comes from here — never from the form —
 * so a tampered request cannot buy a delivery for free.
 */
class ShippingZones
{
    /** @var Collection<int, array{name: string, label: string, fee: float, delivery_days: int}>|null */
    private ?Collection $zones = null;

    /**
     * @return Collection<int, array{name: string, label: string, fee: float, delivery_days: int}>
     */
    public function all(): Collection
    {
        return $this->zones ??= collect(Setting::query()->where('key', 'shipping_zones')->first()?->value ?? [])
            ->values()
            ->map(fn (array $zone) => [
                'name' => (string) ($zone['name'] ?? ''),
                'label' => (string) (app()->getLocale() === 'en' ? ($zone['name_en'] ?? $zone['name'] ?? '') : ($zone['name'] ?? '')),
                'fee' => (float) ($zone['fee'] ?? 0),
                'delivery_days' => (int) ($zone['delivery_days'] ?? 0),
            ])
            ->filter(fn (array $zone) => $zone['name'] !== '')
            ->values();
    }

    /**
     * @return array{name: string, label: string, fee: float, delivery_days: int}|null
     */
    public function find(?string $name): ?array
    {
        if ($name === null || $name === '') {
            return null;
        }

        return $this->all()->firstWhere('name', $name);
    }

    public function feeFor(?string $name): float
    {
        return (float) ($this->find($name)['fee'] ?? 0);
    }

    /**
     * Validation rule source: the canonical zone names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return $this->all()->pluck('name')->all();
    }
}
