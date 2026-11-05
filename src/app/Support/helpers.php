<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;

if (! function_exists('money')) {
    /**
     * Store price in the active locale, e.g. "1 250,00 GH₵" (fr) / "GH₵1,250.00" (en).
     */
    function money(float|string|null $amount, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $intlLocale = config("saraki.currency_locale.{$locale}") ?? config('saraki.currency_locale.en');

        return (new NumberFormatter($intlLocale, NumberFormatter::CURRENCY))
            ->formatCurrency((float) $amount, config('saraki.currency'));
    }
}

if (! function_exists('localized_date')) {
    /**
     * Long date in the active locale and store timezone, e.g. "5 novembre 2026".
     */
    function localized_date(Carbon|string|null $date, ?string $locale = null): string
    {
        if ($date === null) {
            return '';
        }

        $locale ??= app()->getLocale();
        $intlLocale = config("saraki.currency_locale.{$locale}") ?? config('saraki.currency_locale.en');
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date)->timezone(config('app.timezone'));

        return IntlDateFormatter::create(
            $intlLocale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            config('app.timezone'),
        )->format($carbon->toDateTime());
    }
}

if (! function_exists('localized_time')) {
    /**
     * 24-hour time, e.g. "14:30".
     */
    function localized_time(Carbon|string|null $time): string
    {
        if ($time === null) {
            return '';
        }

        return ($time instanceof Carbon ? $time : Carbon::parse($time))->format('H:i');
    }
}
