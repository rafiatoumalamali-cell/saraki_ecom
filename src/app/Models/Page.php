<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title_fr', 'title_en', 'body_fr', 'body_en', 'is_published'])]
class Page extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Slugs the storefront links to. They stay editable but cannot be deleted, and
     * renaming one silently breaks a footer link — the admin list says so.
     */
    public const FIXED_SLUGS = ['about', 'shipping', 'returns', 'privacy', 'terms'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function isPublished(): bool
    {
        return $this->is_published;
    }

    public function url(?string $locale = null): string
    {
        return route('pages.show', ['locale' => $locale ?? app()->getLocale(), 'page' => $this->slug]);
    }
}
