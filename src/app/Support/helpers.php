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
if (!function_exists('remember_group_shared')) {
    /**
     * ĐỌC cache payload đã serialize sẵn (JSON string) theo cùng quy ước
     * key với remember_group(): {$group}:v{version}:{$key}.
     *
     * Chiến lược "cache-first, refresh-on-expire" cho 2 endpoint Flash Sale:
     *   - Còn hạn (TTL 180s = đúng chu kỳ poll 3 phút của flash-live.js)
     *     => hit=true, trả thẳng chuỗi JSON từ file cache, KHÔNG đụng DB (<100ms).
     *   - Hết hạn => hit=false; nếu đã có request khác đang rebuild (marker
     *     @refresh còn sống) thì trả bản cũ @stale NGAY để client không chờ.
     *
     * @return array{hit: bool, stale: bool, raw: string|null}
     */
    function remember_group_shared(string $group, string $key): array
    {
        $version = (int) Cache::get(group_version_key($group), 1);
        $payloadKey = "{$group}:v{$version}:{$key}";
        $markerKey = "{$payloadKey}@refresh";

        $raw = Cache::get($payloadKey);

        if (is_string($raw)) {
            return ['hit' => true, 'stale' => false, 'raw' => $raw];
        }

        // Bản cũ (cũng gắn prefix version => bump_group_version xóa sạch khi admin sửa deal)
        $staleRaw = Cache::get("{$payloadKey}@stale");
        $marker = Cache::get($markerKey);

        // Marker còn = có tiến trình khác đang rebuild => chỉ 1 request đánh DB
        if (is_string($marker)) {
            if (is_string($staleRaw)) {
                return ['hit' => false, 'stale' => true, 'raw' => $staleRaw];
            }

            // Cold start: chưa từng có cache => nhường, chờ ngắn rồi đọc lại
            usleep(200000);
            $fresh = Cache::get($payloadKey);

            return [
                'hit' => is_string($fresh),
                'stale' => false,
                'raw' => is_string($fresh) ? $fresh : null,
            ];
        }

        // Chưa có marker => xin quyền rebuild cho chính request này (client check -> refresh)
        if (!Cache::add($markerKey, (string) time(), 30)) {
            return ['hit' => false, 'stale' => is_string($staleRaw), 'raw' => is_string($staleRaw) ? $staleRaw : null];
        }

        return ['hit' => false, 'stale' => is_string($staleRaw), 'raw' => is_string($staleRaw) ? $staleRaw : null];
    }
}

if (!function_exists('put_group_shared')) {
    /**
     * Ghi payload JSON đã serialize vào cache file:
     *   - Key chính TTL $ttl (mặc định 180s = 3 phút) => mọi request trong 3 phút
     *     sau đó trả thẳng từ file, không query DB.
     *   - Bản sao @stale TTL $staleTtl => lần hết hạn kế tiếp vẫn phục vụ ngay
     *     trong lúc rebuild.
     *   - Xóa marker @refresh => chu kỳ 3 phút sau được phép refresh lại.
     */
    function put_group_shared(string $group, string $key, string $raw, int $ttl, int $staleTtl = 86400): void
    {
        $version = (int) Cache::get(group_version_key($group), 1);
        $payloadKey = "{$group}:v{$version}:{$key}";

        Cache::put($payloadKey, $raw, $ttl);
        Cache::put("{$payloadKey}@stale", $raw, $staleTtl);
        Cache::forget("{$payloadKey}@refresh");
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
