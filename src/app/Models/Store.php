<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(StoreFactory::class)]
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'address', 'phone', 'hours', 'status'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}