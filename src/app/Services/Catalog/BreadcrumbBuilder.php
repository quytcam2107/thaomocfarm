<?php

declare(strict_types=1);

namespace App\Services\Catalog;

/*
 * BreadcrumbBuilder — sinh breadcrumb thường + schema.org BreadcrumbList
 * (tách từ CatalogService; trước đây đoạn array này lặp lại 3 lần).
 */
class BreadcrumbBuilder
{
    /**
     * Breadcrumb hiển thị: phần tử cuối là trang hiện tại (url = null).
     *
     * @param list<array{label: string, url: string|null}> $crumbs
     * @return list<array{label: string, url: string|null}>
     */
    public static function withCurrent(array $crumbs): array
    {
        if ($crumbs !== []) {
            $crumbs[count($crumbs) - 1]['url'] = null;
        }

        return $crumbs;
    }

    /**
     * JSON-LD BreadcrumbList cho SEO.
     *
     * @param list<array{label: string, url: string|null}> $crumbs
     * @return array<string, mixed>
     */
    public static function schema(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn(int $i, array $c): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c['label'],
                    'item' => $c['url'],
                ],
                array_keys($crumbs),
                $crumbs
            )),
        ];
    }
}