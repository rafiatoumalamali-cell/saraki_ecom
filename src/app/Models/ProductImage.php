<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'path', 'thumb_path', 'alt_fr', 'alt_en', 'sort_order', 'is_primary', 'color'])]
class ProductImage extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Shared by the admin upload form and the feature tests so the two limits
     * cannot drift. Gallery photos get a larger cap than account avatars.
     *
     * @var array<int, string>
     */
    public const UPLOAD_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    protected $attributes = [
        'sort_order' => 0,
        'is_primary' => false,
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): ?string
    {
        return $this->path === null ? null : $this->diskUrl($this->path);
    }

    /**
     * Card-sized derivative generated at upload time. Images uploaded before
     * thumbnails existed have no thumb_path and simply serve the full file.
     */
    public function thumbUrl(): ?string
    {
        return $this->thumb_path === null ? $this->url() : $this->diskUrl($this->thumb_path);
    }

    private function diskUrl(string $path): string
    {
        // The Storage facade only advertises the Filesystem contract, which has no
        // url(); the public disk is always an adapter, which does.
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }

    /**
     * Alt text follows the reader's language, falling back to the product name so
     * screen readers never announce "image".
     */
    public function altText(): string
    {
        return $this->translate('alt') ?? $this->product?->translate('name') ?? '';
    }

    public function deleteFiles(): void
    {
        Storage::disk('public')->delete(array_filter([$this->path, $this->thumb_path]));
    }
}
