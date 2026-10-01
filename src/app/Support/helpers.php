<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

if (!function_exists('group_version_key')) {
    /**
     * Key lưu version hiện tại của một nhóm cache
     */
    function group_version_key(string $group): string
    {
        return "thaomoc:group_version:{$group}";
    }
}

if (!function_exists('remember_group')) {
    /**
     * Cache closure theo nhóm có version: key = {group}:v{version}:{key}.
     * Khi bump version, toàn bộ key cũ của nhóm tự động vô hiệu
     * (thay thế cho Cache::tags() vì driver file không hỗ trợ tags).
     */
    function remember_group(string $group, string $key, int $ttl, Closure $callback): mixed
    {
        $version = (int) Cache::get(group_version_key($group), 1);

        return Cache::remember("{$group}:v{$version}:{$key}", $ttl, $callback);
    }
}

if (!function_exists('bump_group_version')) {
    /**
     * Tăng version nhóm cache => mọi block thuộc nhóm hết hạn ngay lập tức.
     * Trả về version mới.
     */
    function bump_group_version(string $group): int
    {
        $next = (int) Cache::get(group_version_key($group), 1) + 1;

        Cache::forever(group_version_key($group), $next);

        return $next;
    }
}
if (!function_exists('format_vnd')) {
    /**
     * Format tiền VND: 800000 => "800.000₫"
     */
    function format_vnd(int $amount): string
    {
        return number_format($amount, 0, ',', '.') . '₫';
    }
}

if (!function_exists('format_number_compact')) {
    /**
     * Format số gọn kiểu UI: 3100 => "3.1k", 2700 => "2.7k", 1200000 => "1.2m"
     */
    function format_number_compact(int $number): string
    {
        if ($number >= 1_000_000) {
            return rtrim(rtrim(number_format($number / 1_000_000, 1, '.', ''), '0'), '.') . 'm';
        }

        if ($number >= 1_000) {
            return rtrim(rtrim(number_format($number / 1_000, 1, '.', ''), '0'), '.') . 'k';
        }

        return (string) $number;
    }
}

if (!function_exists('date_iso8601_from_unix')) {
    /**
     * FIX ĐỒNG BỘ FLASH SALE: unix giây -> ISO8601 theo múi giờ app (đồng nhất
     * với $promotion->end_at->toIso8601String() trước đây trong HomeService).
     */
    function date_iso8601_from_unix(int $unix): string
    {
        return \Illuminate\Support\Carbon::createFromTimestamp($unix)->toIso8601String();
    }
}