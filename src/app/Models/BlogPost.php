<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'author_id', 'title_fr', 'title_en', 'slug', 'excerpt_fr', 'excerpt_en', 'body_fr', 'body_en', 'cover_image',
])]
class BlogPost extends Model
{
    use HasFactory, HasTranslations;

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
}
