<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['categories', 'products'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('slug');
                $table->string('status');
                $table->timestamps();
            });
        }
    }

    public function test_sitemap_has_a_dedicated_route_and_preserves_category_alias(): void
    {
        $routes = Route::getRoutes();

        $this->assertSame(
            SitemapController::class.'@index',
            $routes->match(Request::create('/sitemap.xml'))->getActionName(),
        );
        $this->assertSame(
            CategoryController::class.'@show',
            $routes->match(Request::create('/thao-moc'))->getActionName(),
        );
    }

    public function test_sitemap_contains_only_active_canonical_catalog_urls(): void
    {
        foreach (['categories', 'products'] as $table) {
            DB::table($table)->insert([
                ['slug' => 'cong-khai', 'status' => 'active', 'updated_at' => '2026-10-13 12:00:00'],
                ['slug' => 'ban-nhap', 'status' => 'draft', 'updated_at' => null],
                ['slug' => 'da-an', 'status' => 'hidden', 'updated_at' => null],
            ]);
        }

        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urls = array_map(static fn ($loc): string => (string) $loc, $xml->xpath('//s:loc'));

        $this->assertCount(11, $urls);
        $this->assertContains(route('web.home'), $urls);
        $this->assertContains(route('web.category.show', 'cong-khai'), $urls);
        $this->assertContains(route('web.product.show', 'cong-khai'), $urls);
        $this->assertNotContains(route('web.category.alias', 'cong-khai'), $urls);
        $this->assertStringNotContainsString('ban-nhap', $response->getContent());
        $this->assertStringNotContainsString('da-an', $response->getContent());
        $this->assertCount(2, $xml->xpath('//s:lastmod'));
    }

    public function test_empty_catalog_still_returns_a_valid_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->assertCount(9, $xml->xpath('//s:loc'));
        $this->assertCount(0, $xml->xpath('//s:lastmod'));
    }
}
