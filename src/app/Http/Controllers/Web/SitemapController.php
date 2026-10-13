<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use XMLWriter;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $writeUrl = static function (string $url, ?string $lastModified = null) use ($xml): void {
            $xml->startElement('url');
            $xml->writeElement('loc', $url);

            if ($lastModified !== null) {
                $xml->writeElement('lastmod', $lastModified);
            }

            $xml->endElement();
        };

        foreach ([
            'web.home',
            'web.products.index',
            'web.page.about',
            'web.page.contact',
            'web.page.order-guide',
            'web.page.return-policy',
            'web.page.privacy',
            'web.page.terms',
            'web.blog.index',
        ] as $routeName) {
            $writeUrl(route($routeName));
        }

        foreach (Category::query()
            ->where('status', 'active')
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById(500) as $category) {
            $writeUrl(
                route('web.category.show', $category->slug),
                $category->updated_at?->toAtomString(),
            );
        }

        foreach (Product::query()
            ->where('status', 'active')
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById(500) as $product) {
            $writeUrl(
                route('web.product.show', $product->slug),
                $product->updated_at?->toAtomString(),
            );
        }

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
