<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(ProductVariantFactory::class)]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'sku',
        'label',
        'price',
        'compare_price',
        'stock',
        'is_default',
        'sort_order',
    ];

    /** Quan hệ app-level với product */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayPrice(): int
    {
        return $this->price;
    }

    public function formattedPrice(): string
    {
        return number_format($this->price, 0, ',', '.') . '₫';
    }

    public function formattedComparePrice(): ?string
    {
        return $this->compare_price
            ? number_format($this->compare_price, 0, ',', '.') . '₫'
            : null;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }
}