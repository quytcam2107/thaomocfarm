<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(PromotionFactory::class)]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'type',
        'name',
        'description',
        'start_at',
        'end_at',
        'status',
    ];

    /** @return HasMany<PromotionProduct, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(PromotionProduct::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFlashSale(Builder $query): Builder
    {
        return $query->where('type', 'flash_sale')->active();
    }

    /** Scope: flash sale đang diễn ra tại thời điểm gọi */
    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        $now = now();
        return $query->active()
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }
}