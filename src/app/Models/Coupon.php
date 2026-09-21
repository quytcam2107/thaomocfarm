<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CouponFactory::class)]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'max_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'per_user_limit',
        'status',
        'description',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeValid(Builder $query): Builder
    {
        $now = now();
        return $query->active()
            ->where(fn(Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn(Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now));
    }

    public function scopeDisplayable(Builder $query): Builder
    {
        return $query->valid()->where(function (Builder $q): void {
            $q->whereNull('usage_limit')
                ->orWhereRaw('(SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = coupons.id) < usage_limit');
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}