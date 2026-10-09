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
        'schema_json',
        'cover',
        'reading_minutes',
        'status',
        'published_at',
        'view_count',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

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
    /**
     * JSON-LD của bài viết (cột schema_json) đã chuẩn hóa để Blade render an toàn.
     * - Rỗng / JSON sai cú pháp => null (component tự ẩn, không làm chết trang).
     * - Luôn re-encode bằng json_encode: mọi '@context', '@type'... trong DB trở
     *   thành chuỗi PHP bình thường => Blade KHÔNG hiểu nhầm là directive (@if...)
     *   như khi paste nguyên script ld+json vào file .blade.php.
     * - Escape '<' và '>' thành \u003C/\u003E để nội dung không phá vỡ thẻ <script>.
     */
    public function faqSchemaHtml(): ?string
    {
        $raw = trim((string) ($this->schema_json ?? ''));

        if ($raw === '') {
            return null;
        }

        // Cho phép biên tập viên dán cả khối "<script type="application/ld+json">...</script>"
        // vào cột DB — bóc tag trước khi decode.
        if (stripos($raw, '<script') === 0) {
            $raw = (string) preg_replace('~</?script[^>]*>~i', '', $raw);
            $raw = trim($raw);
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Re-encode: giữ tiếng Việt (UNESCAPED_UNICODE), gọn nhẹ cho SEO bot
        $json = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return null;
        }

        // Chống breakout thẻ <script>: escape '<' '>' thành \u003C/\u003E (vẫn là JSON hợp lệ)
        $json = str_replace(['<', '>'], ['\\u003C', '\\u003E'], $json);

        return $json;
    }
}