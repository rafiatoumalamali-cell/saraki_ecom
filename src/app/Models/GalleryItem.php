<?php

namespace App\Models;

use App\Enums\GalleryCategory;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title_fr', 'title_en', 'category', 'image_path', 'sort_order', 'is_published'])]
class GalleryItem extends Model
{
    use HasFactory, HasTranslations;

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
}
