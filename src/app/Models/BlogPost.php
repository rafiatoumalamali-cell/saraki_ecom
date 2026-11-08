<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'author_id', 'title_fr', 'title_en', 'slug', 'excerpt_fr', 'excerpt_en', 'body_fr', 'body_en', 'cover_image',
])]
class BlogPost extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Shared by the admin form and the feature tests so the two limits cannot
     * drift. Covers are shown wide, so they allow more than a product thumbnail.
     *
     * @var array<int, string>
     */
    public const COVER_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'];

    protected $attributes = [
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)->orderByDesc('published_at');
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published;
    }

    public function url(?string $locale = null): string
    {
        return route('journal.show', ['locale' => $locale ?? app()->getLocale(), 'post' => $this->slug]);
    }

    public function coverUrl(): ?string
    {
        // The Storage facade only advertises the Filesystem contract, which has no
        // url(); the public disk is always an adapter, which does.
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $this->cover_image === null ? null : $disk->url($this->cover_image);
    }

    /**
     * A published article is dated; the date shown to readers is the publish date,
     * falling back to the creation date for posts published in one step.
     */
    public function publishedOn(): ?Carbon
    {
        return $this->published_at ?? ($this->isPublished() ? $this->created_at : null);
    }

    /**
     * Replacing the cover removes the file it superseded so the disk does not fill
     * with orphaned images from repeat edits.
     */
    public function storeCover(UploadedFile $file): void
    {
        $previous = $this->cover_image;

        $this->forceFill(['cover_image' => $file->store('content/posts', 'public')])->save();

        if ($previous !== null) {
            Storage::disk('public')->delete($previous);
        }
    }

    public function deleteCover(): void
    {
        if ($this->cover_image !== null) {
            Storage::disk('public')->delete($this->cover_image);
        }
    }
}
