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
            usleep(150000); // 150ms

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

if (!function_exists('render_cms_html')) {
    /**
     * FIX CMS HTML: nội dung mô tả/bài viết lưu trong DB có thể chứa Blade
     * expression dạng {{ asset('...') }} do người biên tập paste template vào.
     * Khi render bằng {!! ... !!} Blade KHÔNG compile lại chuỗi data => browser
     * in nguyên văn "{{ asset(...) }}" và ảnh không hiển thị.
     *
     * Hàm này:
     *   1. Compile các expression Blade hợp lệ bên trong HTML (chỉ {{ }} —
     *      @{{ }} được Blade hiểu là escape nên không bị thực thi).
     *   2. Sanitize whitelist tag/attr để chống XSS từ nội dung nhúng.
     *   3. Cache kết quả theo md5 (driver file, TTL 1 ngày) để không compile
     *      lại mỗi request.
     *
     * Trả về HtmlString — dùng trực tiếp trong {!! render_cms_html($desc) !!}.
     */
    function render_cms_html(?string $html, string $group = 'catalog'): \Illuminate\Support\HtmlString
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return new \Illuminate\Support\HtmlString('');
        }

        /* Chỉ compile khi phát hiện có Blade expression — tránh chi phí không
           cần cho HTML thường và tránh Blade::render nuốt ký tự '@' (email...)
           trong nội dung.
           FIX BUG "Unknown modifier ')'": trước đây regex dùng '#' làm delimiter
           mà bản thân pattern cũng chứa '#' (nhánh match anchor '#top' trong
           url-safe) => PHP cắt pattern sớm tại dấu '#' thứ 2 và coi ')...' là
           modifier lỗi. Nay dùng delimiter '~' (không xuất hiện trong mọi
           pattern bên dưới) + escape \\{ \\} + nhóm không bắt (?:...).
           Lookahead (?<!@): bỏ qua "@{{ ... }}" (Blade escaped literal,
           không cần compile). */
        $bladeExprRegex = '~(?<!@)\{\{[^{}]*?\}\}~s';
        $needCompile = preg_match($bladeExprRegex, $html) === 1;

        $cacheKey = 'cms_html:' . md5(($needCompile ? 'c' : 'r') . ':' . $html);

        $out = remember_group($group, $cacheKey, 86400, function () use ($html, $needCompile): string {
            $rendered = $needCompile ? \Illuminate\Support\Facades\Blade::render($html) : $html;

            // Sanitize whitelist: giữ tag định dạng thường dùng trong mô tả
            $allowed = '<p><br><b><strong><i><em><u><s><ul><ol><li><h1><h2><h3><h4><h5><h6>'
                . '<a><img><table><thead><tbody><tr><td><th><blockquote><pre><code>'
                . '<div><span><hr><details><summary><figure><figcaption>';

            $cleaned = strip_tags($rendered, $allowed);

            // Loại mọi handler on* (onclick/onerror...) — chống XSS qua attribute
            $cleaned = preg_replace('~\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i', '', $cleaned);

            /* Validate href/src: chỉ cho http(s), absolute path '/', assets/,
               storage/, anchor '#...' và data:image/.
               CÙNG FIX DELIMITER: pattern chứa cả '/' lẫn '#' nên dùng '~'
               (không xung đột) + (?:...) để alternation không nuốt nhầm nhánh. */
            $urlSafeRegex = '~^(?:https?://|/|assets/|storage/|\#|data:image/)~i';

            $cleaned = preg_replace_callback(
                '~\s(href|src)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))~i',
                function (array $m) use ($urlSafeRegex): string {
                    $attr = strtolower($m[1]);
                    $url = trim($m[3] ?? $m[4] ?? $m[5] ?? '');

                    $ok = $url === '' || preg_match($urlSafeRegex, $url) === 1;

                    return $ok
                        ? ' ' . $attr . '="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
                        : '';
                },
                $cleaned
            );

            return (string) $cleaned;
        });

        return new \Illuminate\Support\HtmlString((string) $out);
    }
}