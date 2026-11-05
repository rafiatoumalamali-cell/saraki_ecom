<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'size', 'color', 'sku', 'stock_qty', 'price_delta', 'is_active'])]
class ProductVariant extends Model
{
    use HasFactory;

    public const DEFAULT = 'Default';

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
}
