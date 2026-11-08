<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * The settings table is a key/value store, so every reader would otherwise repeat
 * the same `Setting::where('key', …)` dance. Values are loaded once per request,
 * which also keeps the footer, the WhatsApp button and the checkout reading the
 * same snapshot. Admin saves refresh that snapshot immediately.
 */
class Settings
{
    /** @var array<string, mixed>|null */
    private ?array $values = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values ??= Setting::query()->pluck('value', 'key')->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * A trimmed string, or null when the value is missing or blank. Settings are
     * typed as JSON, so an empty text field arrives as "" rather than null.
     */
    public function text(string $key): ?string
    {
        $value = $this->get($key);

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function put(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);

        $this->values = null;
    }

    /**
     * Saves a whole form in one transaction, then drops the snapshot once instead
     * of once per key.
     *
     * @param  array<string, mixed>  $values
     */
    public function putMany(array $values, string $group): void
    {
        DB::transaction(function () use ($values, $group) {
            foreach ($values as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
            }
        });

        $this->values = null;
    }

    public function forget(): void
    {
        $this->values = null;
    }

    /**
     * Social profiles that have a URL, ready for the footer.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $configured = $this->get('social_links');

        if (! is_array($configured)) {
            return [];
        }

        return collect($configured)
            ->filter(fn ($url) => is_string($url) && trim($url) !== '')
            ->map(fn (string $url) => trim($url))
            ->all();
    }

    /**
     * wa.me wants the international number without punctuation, which is exactly
     * what the admin may not type ("+233 24 000 0000").
     */
    public function whatsappNumber(): ?string
    {
        $number = $this->text('whatsapp_number');

        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        // Too short to be an international number, so the button stays off rather
        // than opening a chat that cannot exist.
        return strlen($digits) < 8 ? null : $digits;
    }

    public function whatsappLink(?string $message = null): ?string
    {
        $number = $this->whatsappNumber();

        if ($number === null) {
            return null;
        }

        $message ??= (string) __('ui.whatsapp_message');

        return 'https://wa.me/'.$number.($message === '' ? '' : '?text='.rawurlencode($message));
    }

    /**
     * The map is stored as the embed URL, but admins pasting Google's "share"
     * snippet paste a whole iframe. The src is lifted out of that markup and the
     * result is refused unless it is a plain http(s) address — the value ends up
     * in an iframe src, so it must never be raw HTML.
     */
    public function mapEmbedUrl(): ?string
    {
        $value = $this->text('map_embed');

        return $value === null ? null : self::embedUrl($value);
    }

    /**
     * Shared with the admin form so a value the request accepts is exactly a value
     * the storefront will render.
     */
    public static function embedUrl(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $value, $matches) === 1) {
            $value = html_entity_decode($matches[1]);
        }

        $isUrl = filter_var($value, FILTER_VALIDATE_URL) !== false;

        return $isUrl && preg_match('#^https?://#i', $value) === 1 ? $value : null;
    }
}
