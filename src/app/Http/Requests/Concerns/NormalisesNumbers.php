<?php

namespace App\Http\Requests\Concerns;

trait NormalisesNumbers
{
    /**
     * Prices are typed by hand in a French-first admin, where "1 250,00" is the
     * natural notation. Strip the thousand separators and read the comma as a
     * decimal point before the numeric rules run.
     */
    protected function decimal(string $key): ?string
    {
        $value = $this->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(["\u{00A0}", "\u{202F}", ' '], '', (string) $value);

        return str_replace(',', '.', $value);
    }
}
