<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'size', 'color', 'sku', 'stock_qty', 'price_delta', 'is_active'])]
class ProductVariant extends Model
{
    use HasFactory;

    public const DEFAULT = 'Default';

    protected $attributes = [
        'stock_qty' => 0,
        'price_delta' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'stock_qty' => 'integer',
            'price_delta' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function price(): Attribute
    {
        return Attribute::get(fn () => number_format((float) $this->product->base_price + (float) $this->price_delta, 2, '.', ''));
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => collect([$this->size, $this->color])
            ->reject(fn ($part) => $part === self::DEFAULT)
            ->implode(' / '));
    }

    public function isInStock(): bool
    {
        return $this->is_active && $this->stock_qty > 0;
    }

    /**
     * "Made to order" stand-in for products that are not split into size/colour
     * combinations, so the storefront never shows an empty selector.
     */
    public function isDefault(): bool
    {
        return $this->size === self::DEFAULT && $this->color === self::DEFAULT;
    }
}
