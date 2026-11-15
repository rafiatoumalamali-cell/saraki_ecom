<?php

namespace App\Models;

use App\Enums\GalleryCategory;
use App\Models\Concerns\HasTranslations;
use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title_fr', 'title_en', 'category', 'image_path', 'thumb_path', 'sort_order', 'is_published'])]
class GalleryItem extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Shared by the admin form and the feature tests so the two limits cannot
     * drift. Gallery photos are shown full-bleed, so they get a larger cap than
     * product thumbnails.
     *
     * @var array<int, string>
     */
    public const UPLOAD_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'];

    protected $attributes = [
        'category' => 'collection',
        'sort_order' => 0,
        'is_published' => true,
    ];

    protected function casts(): array
    {
        return [
            'category' => GalleryCategory::class,
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function url(): string
    {
        return $this->diskUrl($this->image_path);
    }

    /**
     * Card-sized derivative for grids; items uploaded before thumbnails
     * existed simply serve the full file.
     */
    public function thumbUrl(): string
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
     * Replacing the photo leaves the previous file behind unless it is removed by
     * hand, so both the edit form and the delete action go through here.
     */
    public function storeImage(UploadedFile $file): void
    {
        $previous = [$this->image_path, $this->thumb_path];

        $stored = app(ImageOptimizer::class)->store($file, 'content/gallery');

        $this->forceFill([
            'image_path' => $stored['path'],
            'thumb_path' => $stored['thumb'],
        ])->save();

        $this->deletePaths($previous);
    }

    public function deleteFiles(): void
    {
        $this->deletePaths([$this->image_path, $this->thumb_path]);
    }

    /**
     * @param  array<int, ?string>  $paths
     */
    private function deletePaths(array $paths): void
    {
        Storage::disk('public')->delete(array_filter($paths));
    }
}
