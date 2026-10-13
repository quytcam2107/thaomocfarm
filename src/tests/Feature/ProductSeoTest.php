<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\ProductViewDTO;
use App\Services\Catalog\ProductDetailFetcher;
use App\Services\Catalog\ProductDetailHydrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->integer('category_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('sku');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('status');
            $table->json('seo')->nullable();
            $table->integer('price_min')->default(100000);
            $table->integer('compare_price')->default(0);
            $table->integer('stock_total')->default(10);
            $table->float('rating_avg')->default(5);
            $table->integer('rating_count')->default(1);
            $table->integer('sold_count')->default(0);
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
        });
        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->integer('product_id');
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->string('alt')->nullable();
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
        });
        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->integer('product_id');
            $table->string('label');
            $table->integer('price');
            $table->integer('compare_price')->nullable();
            $table->integer('stock');
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
        });
    }

    public function test_custom_seo_reaches_metadata_and_valid_json_ld(): void
    {
        $title = 'Trà "thảo mộc" & Mộc Xanh';
        $description = 'Mô tả "đặc biệt" & </script><script>alert(1)</script> \\ Tây Bắc';
        $product = $this->productWithSeo(['meta_title' => $title, 'meta_description' => $description]);

        $this->assertSame($title, $product->meta_title);
        $this->assertSame($description, $product->meta_description);

        $html = Blade::render('<x-layouts.app :title="$product->meta_title" :seoDescription="$product->meta_description"><x-slot name="schema"><x-product.schema :product="$product" /></x-slot></x-layouts.app>', ['product' => $product]);

        $this->assertStringContainsString('<title>'.e($title).'</title>', $html);
        $this->assertStringContainsString('<meta property="og:title" content="'.e($title).'">', $html);
        $this->assertStringContainsString('<meta name="description" content="'.e($description).'">', $html);
        $this->assertStringContainsString('<meta property="og:description" content="'.e($description).'">', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $this->assertCount(2, $matches[1]);
        $schema = json_decode($matches[1][0], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($description, $schema['description']);
        $this->assertSame('Sản phẩm mẫu', $schema['name']);
        $this->assertSame('BreadcrumbList', json_decode($matches[1][1], true, 512, JSON_THROW_ON_ERROR)['@type']);
    }

    public function test_null_seo_falls_back_to_name_and_subtitle(): void
    {
        $product = $this->productWithSeo(null);

        $this->assertSame('Sản phẩm mẫu | Mộc Xanh', $product->meta_title);
        $this->assertSame('Mô tả ngắn', $product->meta_description);
    }

    public function test_blank_seo_and_subtitle_fall_back_to_name(): void
    {
        $product = $this->productWithSeo(['meta_title' => '  ', 'meta_description' => "\t"], '  ');

        $this->assertSame('Sản phẩm mẫu | Mộc Xanh', $product->meta_title);
        $this->assertSame('Sản phẩm mẫu', $product->meta_description);
    }

    public function test_invalid_seo_values_fall_back_without_errors(): void
    {
        $product = $this->productWithSeo(['meta_title' => ['invalid'], 'meta_description' => false]);

        $this->assertSame('Sản phẩm mẫu | Mộc Xanh', $product->meta_title);
        $this->assertSame('Mô tả ngắn', $product->meta_description);
    }

    private function productWithSeo(?array $seo, ?string $subtitle = 'Mô tả ngắn'): ProductViewDTO
    {
        DB::table('products')->insert([
            'slug' => 'san-pham-mau',
            'name' => 'Sản phẩm mẫu',
            'sku' => 'SEO-001',
            'status' => 'active',
            'subtitle' => $subtitle,
            'seo' => $seo === null ? null : json_encode($seo, JSON_THROW_ON_ERROR),
        ]);

        return ProductDetailHydrator::hydrate(
            ProductDetailFetcher::fetch('san-pham-mau'),
            ['reviews' => [], 'stats' => []],
        )['product'];
    }
}
