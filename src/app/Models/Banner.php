<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[UseFactory(BannerFactory::class)]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'position',
        'title',
        'subtitle',
        'image',
        'link',
        'sort_order',
        'status',
        'start_at',
        'end_at',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Scope: banner theo vị trí (home_hero, home_mid...) */
    public function scopePosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position)
            ->active()
            ->orderBy('sort_order');
    }

    public function imageUrl(): string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : '';
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