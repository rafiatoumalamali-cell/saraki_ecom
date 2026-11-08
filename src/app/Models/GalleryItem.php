<?php

namespace App\Models;

use App\Enums\GalleryCategory;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title_fr', 'title_en', 'category', 'image_path', 'sort_order', 'is_published'])]
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
        // The Storage facade only advertises the Filesystem contract, which has no
        // url(); the public disk is always an adapter, which does.
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($this->image_path);
    }

    /**
     * Replacing the photo leaves the previous file behind unless it is removed by
     * hand, so both the edit form and the delete action go through here.
     */
    public function storeImage(UploadedFile $file): void
    {
        $previous = $this->image_path;

        $this->forceFill(['image_path' => $file->store('content/gallery', 'public')])->save();

        $this->deleteFile($previous);
    }

    public function deleteFile(?string $path = null): void
    {
        $path ??= $this->image_path;

        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
