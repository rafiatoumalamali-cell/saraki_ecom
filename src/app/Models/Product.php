<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'type', 'name_fr', 'name_en', 'slug', 'description_fr', 'description_en', 'base_price', 'base_sku', 'fabric_price', 'fabric_unit', 'confection_price', 'is_active', 'is_featured'])]
class Product extends Model
{
    use HasFactory, HasTranslations;

    public const TYPE_READY_TO_WEAR = 'ready_to_wear';

    public const TYPE_MADE_TO_MEASURE = 'made_to_measure';

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'fabric_price' => 'decimal:2',
            'confection_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Sur-mesure pieces are not bought off the rack: the customer picks the
     * model and the atelier tailors it, in SARAKI fabric or the client's own.
     */
    public function isMadeToMeasure(): bool
    {
        return $this->type === self::TYPE_MADE_TO_MEASURE;
    }

    /**
     * Sur-mesure pieces carry a price breakdown: the fabric (sold per meter
     * or per pagne) and the tailor's making fee. Both are indicative — the
     * final quote is confirmed in the atelier.
     */
    public function hasTailoringPrices(): bool
    {
        return $this->fabric_price !== null || $this->confection_price !== null;
    }

    /**
     * Storefront search. Every word of the query has to appear in the piece, in
     * either language, so "boubou noir" narrows down instead of widening.
     *
     * The products table carries a FULLTEXT index, but matching through it is
     * deferred until the writing transaction commits, which makes a piece
     * invisible to search while it is being published. At boutique scale a LIKE
     * scan is immediate and returns exactly what the shop has, so it wins on
     * correctness.
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $words = collect(preg_split('/[^\p{L}\p{N}]+/u', trim((string) $term)) ?: [])
            ->filter()
            ->values();

        if ($words->isEmpty()) {
            return;
        }

        $query->where(function (Builder $outer) use ($words) {
            foreach ($words as $word) {
                $like = '%'.addcslashes($word, '%_\\').'%';

                $outer->where(fn (Builder $inner) => $inner
                    ->where('name_fr', 'like', $like)
                    ->orWhere('name_en', 'like', $like)
                    ->orWhere('description_fr', 'like', $like)
                    ->orWhere('description_en', 'like', $like));
            }
        });
    }

    /**
     * Admin search: matches names and the SKU, where operators expect exact
     * lookups more than relevance ranking.
     */
    public function scopeKeyword(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(fn (Builder $inner) => $inner
            ->where('name_fr', 'like', $like)
            ->orWhere('name_en', 'like', $like)
            ->orWhere('base_sku', 'like', $like));
    }

    /**
     * Localised name, used for alphabetical sorting so the list order follows the
     * language the visitor is reading.
     */
    public function scopeOrderByName(Builder $query, ?string $locale = null): void
    {
        $query->orderBy($locale === 'en' ? 'name_en' : 'name_fr');
    }

    /**
     * Lowest price a customer can pay: the base price plus the cheapest active
     * variant delta. Products without variants simply sell at the base price.
     */
    public function priceFrom(): float
    {
        $delta = $this->activeVariants()->min(fn (ProductVariant $variant) => (float) $variant->price_delta);

        return (float) $this->base_price + (float) ($delta ?? 0);
    }

    public function hasVariablePricing(): bool
    {
        return $this->activeVariants()->contains(fn (ProductVariant $variant) => (float) $variant->price_delta !== 0.0);
    }

    public function totalStock(): int
    {
        return (int) $this->activeVariants()->sum('stock_qty');
    }

    public function isSoldOut(): bool
    {
        return $this->totalStock() === 0;
    }

    public function imageUrl(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        $image = $images->firstWhere('is_primary', true) ?? $images->first();

        return $image?->url();
    }

    /**
     * The card-sized derivative of the primary image, for grids and lists.
     */
    public function imageThumbUrl(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        $image = $images->firstWhere('is_primary', true) ?? $images->first();

        return $image?->thumbUrl();
    }

    public function url(?string $locale = null): string
    {
        return route('shop.show', [
            'locale' => $locale ?? app()->getLocale(),
            'product' => $this->slug,
        ]);
    }

    /**
     * A single-paragraph description for meta tags and card previews.
     */
    public function summary(int $limit = 155): ?string
    {
        $description = $this->translate('description');

        return filled($description) ? Str::limit(trim(strip_tags($description)), $limit) : null;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    private function activeVariants(): Collection
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->where('is_active', true)->values();
        }

        return $this->variants()->where('is_active', true)->get();
    }
}
