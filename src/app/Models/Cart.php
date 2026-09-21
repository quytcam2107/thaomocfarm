<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CartFactory::class)]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['user_id', 'cart_token'];

    /** @return HasMany<CartItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Tổng số lượng sản phẩm trong giỏ */
    public function countItems(): int
    {
        return (int) $this->items->sum('qty');
    }
}