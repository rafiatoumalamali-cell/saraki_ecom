<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class Slug
{
    /**
     * Builds a slug that is free in the given table, appending -2, -3 … when the
     * base form is already taken. Editors may type a slug by hand, so the input is
     * normalised rather than trusted: only a-z, 0-9 and single dashes survive.
     */
    public static function unique(?string $source, string $table, ?int $ignoreId = null): string
    {
        $base = Str::slug((string) $source);

        if ($base === '') {
            $base = 'element-'.Str::lower(Str::random(6));
        }

        $candidate = $base;
        $suffix = 2;

        while (self::taken($candidate, $table, $ignoreId)) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private static function taken(string $slug, string $table, ?int $ignoreId): bool
    {
        return DB::table($table)
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
