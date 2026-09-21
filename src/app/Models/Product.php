<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[UseFactory(ProductFactory::class)]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'sku',
        'slug',
        'name',
        'subtitle',
        'description',
        'price_min',
        'compare_price',
        'stock_total',
        'sold_count',
        'rating_avg',
        'rating_count',
        'view_count',
        'is_featured',
        'status',
        'published_at',
        'seo',
    ];

    /** Quan hệ app-level với category: không FK, không auto cascade */
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
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function promotionProducts(): HasMany
    {
        return $this->hasMany(PromotionProduct::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestSeller(Builder $query): Builder
    {
        return $query->orderByDesc('sold_count');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '')
            return $query;

        return $query->where(function (Builder $q) use ($term): void {
            $q->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', [$term])
                ->orWhere('name', 'like', '%' . $term . '%');
        });
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
            ->ofMany([], fn(Builder $q) => $q->where('is_default', true));
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->ofMany([], fn(Builder $q) => $q->where('is_cover', true));
    }

    public function url(): string
    {
        return route('web.product.show', $this->slug);
    }

    public function formattedPriceMin(): string
    {
        return number_format($this->price_min, 0, ',', '.') . '₫';
    }

    public function incrementViews(): void
    {
        $this->newQuery()->where('id', $this->id)->increment('view_count');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'seo' => 'array',
            'is_featured' => 'boolean',
        ];
    }
}