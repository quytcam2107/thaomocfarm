<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(PostFactory::class)]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'post_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover',
        'reading_minutes',
        'status',
        'published_at',
        'view_count',
    ];

    /** @return BelongsTo<PostCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /** Scope: bài viết đã xuất bản + trong thời hạn hiển thị */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function url(): string
    {
        return route('web.blog.show', $this->slug);
    }

    public function incrementViews(): void
    {
        $this->newQuery()->where('id', $this->id)->increment('view_count');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}