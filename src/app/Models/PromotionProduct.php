<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PromotionProductFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(PromotionProductFactory::class)]
class PromotionProduct extends Model
{
    /** @use HasFactory<PromotionProductFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'promotion_id',
        'product_id',
        'product_variant_id',
        'flash_price',
        'discount_percent',
        'qty_total',
        'qty_sold',
        'per_user_limit',
        'sort_order',
    ];

    /** @return BelongsTo<Promotion, $this> */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<ProductVariant, $this> */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Số slot còn lại (cho progress bar UI flash sale) */
    public function slotsLeft(): int
    {
        return max(0, $this->qty_total - $this->qty_sold);
    }

    /** Phần trăm đã bán */
    public function soldPercent(): int
    {
        return $this->qty_total > 0
            ? (int) round($this->qty_sold * 100 / $this->qty_total)
            : 0;
    }
}