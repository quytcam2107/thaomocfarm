<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(TestimonialFactory::class)]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_name',
        'customer_location',
        'orders_count',
        'rating',
        'content',
        'avatar',
        'sort_order',
        'status',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->orderBy('sort_order');
    }
}