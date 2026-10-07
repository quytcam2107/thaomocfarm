<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tỉnh / thành phố trực thuộc trung ương (cấp hành chính 1).
 * Dữ liệu nạp bởi `php artisan admin:import-locations` từ data/vietnam_2_levels_v2.json.
 */
class Province extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'division_type',
        'codename',
        'phone_code',
    ];

    /** @return HasMany<Ward> Quan hệ theo mã tỉnh, không dùng id nội bộ */
    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class, 'province_code', 'code');
    }

    /** Sắp xếp theo tên tăng dần (đổ dữ liệu vào select box tỉnh/thành). */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name')->orderBy('code');
    }

    /** Bỏ bớt phần loại đứng trước ("Thành phố Hồ Chí Minh" → "Hồ Chí Minh"). */
    public function shortName(): string
    {
        return (string) preg_replace('/^(Thành phố|Tỉnh)\s+/iu', '', $this->name);
    }
}