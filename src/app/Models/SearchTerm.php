<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchTermFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[UseFactory(SearchTermFactory::class)]
class SearchTerm extends Model
{
    /** @use HasFactory<SearchTermFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['term', 'hits'];

    public static function track(string $term): void
    {
        $term = trim(mb_strtolower($term));
        if ($term === '' || mb_strlen($term) < 2)
            return;

        self::updateOrCreate(
            ['term' => $term],
            ['hits' => DB::raw('hits + 1')]
        );
    }
}