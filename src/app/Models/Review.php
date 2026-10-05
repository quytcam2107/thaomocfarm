<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ReviewFactory::class)]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'ip_address',
        'rating',
        'content',
        'images',
        'is_verified',
        'admin_reply',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** NEW: lượt bấm "Hữu ích" (đếm qua withCount trong ReviewService) */
    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_verified' => 'boolean',
        ];
    }
}