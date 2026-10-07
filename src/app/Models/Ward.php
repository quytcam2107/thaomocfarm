<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Xã / phường / đặc khu (cấp hành chính 2).
 * Quan hệ với Province qua cột province_code ↔ provinces.code (không dùng id nội bộ).
 * ⚠️ code là khóa duy nhất toàn quốc; name/codename CÓ THỂ TRÙNG giữa các tỉnh.
 */
class Ward extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'division_type',
        'codename',
        'province_code',
        'province_name',
    ];

    /** @return BelongsTo<Province, Ward> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /** Lọc phường theo tỉnh — dùng cho cascading select / API sau này. */
    public function scopeOfProvince(Builder $query, int|string|null $provinceCode): Builder
    {
        return $query->where('province_code', $provinceCode);
    }

    /** Sắp xếp theo tên tăng dần. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name')->orderBy('code');
    }

    /** Bỏ bớt phần loại đứng trước ("Phường Ba Đình" → "Ba Đình"). */
    public function shortName(): string
    {
        return (string) preg_replace('/^(Phường|Xã|Đặc khu)\s+/iu', '', $this->name);
    }
}