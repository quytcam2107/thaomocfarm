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