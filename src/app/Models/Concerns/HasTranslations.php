<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locales = array_unique([$locale ?? app()->getLocale(), ...config('saraki.supported_locales')]);

        foreach ($locales as $candidate) {
            $value = $this->getAttribute("{$field}_{$candidate}");

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }
}
