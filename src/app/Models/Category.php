<?php


namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CategoryFactory::class)]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'description',
        'sort_order', 'is_featured', 'status',
    ];

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Scope: chỉ lấy danh mục đang hoạt động */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Scope: danh mục được ghim lên pills trang chủ */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function url(): string
    {
        return route('web.category.show', $this->slug);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }
}