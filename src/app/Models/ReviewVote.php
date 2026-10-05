<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 * ReviewVote — 1 lượt bấm "Hữu ích" cho 1 đánh giá.
 * Chống trùng bằng UNIQUE (review_id, ip_address) — không FK theo convention.
 */
class ReviewVote extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'review_id',
        'ip_address',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}