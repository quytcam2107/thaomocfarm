<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[UseFactory(ProductImageFactory::class)]
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'path',
        'thumb_path',
        'alt',
        'sort_order',
        'is_cover',
    ];

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrl(): string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : '';
    }

    public function getThumbUrl(): string
    {
        $path = $this->thumb_path ?: $this->path;
        return $path ? Storage::disk('public')->url($path) : '';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }
}