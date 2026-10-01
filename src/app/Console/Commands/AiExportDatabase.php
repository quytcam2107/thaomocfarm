<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AiExportDatabase extends Command
{
    protected $signature = 'ai:export-database
                            {--connection= : Kết nối CSDL cần xuất (mặc định: kết nối mặc định trong .env)}
                            {--output= : Thư mục xuất file (mặc định: storage/app/ai-database)}
                            {--include-framework : Xuất cả các bảng framework (cache, jobs, sessions, migrations...)}';

    protected $description = 'Xuất cấu trúc + TOÀN BỘ dữ liệu của mọi bảng trong database ra các file Markdown/JSONL (mỗi bảng 1 file) để AI đọc hiểu. Chỉ đọc (SELECT), không thay đổi dữ liệu. Chạy lại lệnh bất cứ khi nào muốn cập nhật bản xuất.';

    private const CHUNK_SIZE = 500;
    private const MAX_DISTINCT_VALUES = 25;
    private const MAX_CELL_LENGTH = 300;
    private const MAX_TABLE_BYTES = 1_500_000;
    private const SAMPLE_ROWS = 50;

    private const FRAMEWORK_TABLES = [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'sessions',
    ];

    public function handle(): int
    {
        $connectionName = $this->option('connection') ?: config('database.default');
        $connection = DB::connection($connectionName);

        $outputDir = rtrim((string) ($this->option('output') ?: storage_path('app/ai-database')), '/\\');
        foreach ([$outputDir, "{$outputDir}/tables", "{$outputDir}/data"] as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                $this->error("Không thể tạo thư mục xuất: {$dir}");
                return self::FAILURE;
            }
        }

        $driver = $connection->getDriverName();
        $dbName = (string) ($connection->getConfig('database') ?? 'database');

        $this->info("Kết nối    : {$connectionName} (driver: {$driver})");
        $this->info("Database   : {$dbName}");
        $this->info("Thư mục xuất: {$outputDir}");
        $this->newLine();

        $tables = $this->discoverTables($connection, $dbName, $driver);

        if (!$this->option('include-framework')) {
            $tables = array_values(array_filter(
                $tables,
                fn($t) => !in_array(strtolower($t), self::FRAMEWORK_TABLES, true)
            ));
        }

        if (empty($tables)) {
            $this->warn('Không tìm thấy bảng nào để xuất.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        $manifest = [];
        $exitCode = self::SUCCESS;

        foreach ($tables as $table) {
            try {
                $columns = $this->readColumns($connection, $table);
                $indexes = $this->readIndexes($connection, $driver, $table);
                $foreignKeys = $this->readForeignKeys($connection, $table);
                [$total, $hasData] = $this->dumpRows($connection, $table, $columns, "{$outputDir}/data/{$table}.jsonl");

                $stats = $this->buildStats($connection, $table, $columns, $total);

                $meta = [
                    'table' => $table,
                    'comment' => $this->readTableComment($connection, $table),
                    'row_count' => $total,
                    'columns' => $columns,
                    'indexes' => $indexes,
                    'foreign_keys' => $foreignKeys,
                    'stats' => $stats,
                    'data_file' => $hasData ? "data/{$table}.jsonl" : null,
                ];

                $this->writeTableMarkdown($table, $meta, $connection, "{$outputDir}/tables/{$table}.md");

                $manifest[] = [
                    'table' => $table,
                    'rows' => $total,
                    'file' => "tables/{$table}.md",
                    'data' => $hasData ? "data/{$table}.jsonl" : null,
                ];
            } catch (\Throwable $e) {
                $exitCode = self::FAILURE;
                $this->newLine();
                $this->error("Lỗi khi xuất bảng [{$table}]: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->writeIndexMarkdown($outputDir, $connectionName, $dbName, $driver, $manifest);

        $totalRows = array_sum(array_column($manifest, 'rows'));
        $this->info('Đã xuất ' . count($manifest) . " bảng, {$totalRows} bản ghi.");
        $this->line("Bắt đầu đọc từ: <options=bold>{$outputDir}/INDEX.md</>");

        return $exitCode;
    }

    /**
     * Lấy danh sách bảng qua Schema Builder và chuẩn hoá thành TÊN THÔ:
     * - bỏ qualifier dạng "schema.bảng" / "database.bảng" (sqlite trả về "main.xxx")
     * - bỏ table prefix cấu hình -> dùng lại an toàn với Query Builder (Laravel tự thêm prefix)
     */
    private function discoverTables($connection, string $dbName, string $driver): array
    {
        $prefix = (string) $connection->getTablePrefix();
        $multiSchema = in_array($driver, ['pgsql', 'sqlsrv'], true);

        $tables = [];
        foreach ($connection->getSchemaBuilder()->getTableListing() as $t) {
            $t = (string) $t;

            if (str_contains($t, '.')) {
                [$qualifier, $bare] = explode('.', $t, 2);
                if (!$multiSchema || $qualifier === 'main' || $qualifier === $dbName) {
                    $t = $bare;
                }
            }

            if ($prefix !== '' && str_starts_with($t, $prefix)) {
                $t = substr($t, strlen($prefix));
            }

            if ($t !== '') {
                $tables[] = $t;
            }
        }

        sort($tables);

        return $tables;
    }

    private function readColumns($connection, string $table): array
    {
        return array_map(fn($c) => [
            'name' => (string) $c['name'],
            'type' => strtolower((string) $c['type']),
            'nullable' => (bool) ($c['nullable'] ?? false),
            'default' => $c['default'] ?? null,
            'extra' => !empty($c['autoincrement']) ? 'autoincrement' : '',
            'comment' => (string) ($c['comment'] ?? ''),
        ], $connection->getSchemaBuilder()->getColumns($table));
    }

    private function readIndexes($connection, string $driver, string $table): array
    {
        try {
            return array_map(fn($i) => [
                'name' => (string) $i['name'],
                'unique' => (bool) ($i['unique'] ?? false),
                'type' => (string) ($i['type'] ?? ''),
                'columns' => $i['columns'] ?? [],
            ], $connection->getSchemaBuilder()->getIndexes($table));
        } catch (\Throwable) {
            // Fallback (mysql/mariadb): SHOW INDEX — dùng wrapTable chuẩn của Laravel grammar
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $wrapped = $connection->getQueryGrammar()->wrapTable($table);
                $rows = $connection->select('SHOW INDEX FROM ' . $wrapped);

                $grouped = [];
                foreach ($rows as $r) {
                    $name = (string) $r->Key_name;
                    $grouped[$name]['unique'] = $r->Non_unique == 0;
                    $grouped[$name]['type'] = (string) $r->Index_type;
                    $grouped[$name]['columns'][] = (string) $r->Column_name;
                }

                return array_map(
                    fn($name, $g) => ['name' => $name] + $g,
                    array_keys($grouped),
                    array_values($grouped)
                );
            }

            throw new \RuntimeException("Không đọc được index của bảng [{$table}].");
        }
    }

    private function readForeignKeys($connection, string $table): array
    {
        try {
            return array_map(fn($f) => [
                'name' => (string) $f['name'],
                'column' => implode(', ', $f['columns'] ?? []),
                'references' => "{$f['foreign_table']}.{$f['foreign_column_name']}",
            ], $connection->getSchemaBuilder()->getForeignKeys($table));
        } catch (\Throwable) {
            return [];
        }
    }

    private function readTableComment($connection, string $table): string
    {
        try {
            foreach ($connection->getSchemaBuilder()->getTables() as $t) {
                $n = (string) ($t['name'] ?? '');
                if ($n === $table || str_ends_with($n, ".{$table}") || str_ends_with($n, $table)) {
                    return (string) ($t['comment'] ?? '');
                }
            }
        } catch (\Throwable) {
            // bỏ qua — comment chỉ là thông tin bổ sung
        }

        return '';
    }

    /**
     * Dump TOÀN BỘ dữ liệu ra file JSON Lines (mỗi dòng = 1 bản ghi).
     * Dùng tên bảng thô -> Query Builder tự thêm prefix + quote đúng cách.
     */
    private function dumpRows($connection, string $table, array $columns, string $path): array
    {
        $fh = fopen($path, 'wb');
        if ($fh === false) {
            throw new \RuntimeException("Không thể mở file ghi: {$path}");
        }

        $count = 0;
        try {
            $query = $connection->query()->from($table);

            // chunk() bắt buộc phải có orderBy -> sắp theo cột đầu tiên (thường là PK)
            $firstCol = $columns[0]['name'] ?? null;
            if ($firstCol !== null) {
                $query->orderBy($firstCol);
            }

            $query->chunk(self::CHUNK_SIZE, function ($rows) use ($fh, &$count) {
                foreach ($rows as $row) {
                    fwrite($fh, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    fwrite($fh, "\n");
                    $count++;
                }
            });
        } catch (\Throwable $e) {
            fclose($fh);
            @unlink($path);
            throw $e;
        }

        fclose($fh);

        if ($count === 0) {
            @unlink($path);
            return [0, false];
        }

        return [$count, true];
    }

    private function buildStats($connection, string $table, array $columns, int $total): array
    {
        if ($total === 0) {
            return [];
        }

        $stats = [];

        foreach ($columns as $col) {
            $name = $col['name'];
            $isNumeric = (bool) preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal|numeric|float|double)/', $col['type']);
            $isDate = (bool) preg_match('/^(date|datetime|timestamp|time)/', $col['type']);

            if ($isNumeric || $isDate) {
                $row = $connection->query()->from($table)->selectRaw("MIN({$name}) AS mn, MAX({$name}) AS mx")->first();
                if ($row !== null && ($row->mn !== null || $row->mx !== null)) {
                    $stats[$name] = ['min' => $row->mn, 'max' => $row->mx];
                }
            }

            $distinct = $connection->query()->from($table)->selectRaw("COUNT(DISTINCT {$name}) AS d")->first();
            $d = (int) ($distinct->d ?? 0);
            if ($d > 0 && $d <= self::MAX_DISTINCT_VALUES) {
                $vals = $connection->query()
                    ->from($table)
                    ->selectRaw("{$name} AS v, COUNT(*) AS c")
                    ->groupBy($name)
                    ->orderByDesc('c')
                    ->limit(self::MAX_DISTINCT_VALUES)
                    ->get();

                $stats[$name]['top_values'] = array_map(
                    fn($r) => ['value' => $r->v, 'count' => (int) $r->c],
                    $vals->all()
                );
            }
        }

        return $stats;
    }

    private function writeTableMarkdown(string $table, array $meta, $connection, string $path): void
    {
        $nl = "\n";
        $out = fopen($path, 'wb');
        if ($out === false) {
            throw new \RuntimeException("Không thể mở file ghi: {$path}");
        }

        $w = fn(string $s) => fwrite($out, $s);

        $w("# Bảng `{$table}`{$nl}{$nl}");
        $w('> File này do lệnh `php artisan ai:export-database` sinh tự động — **KHÔNG SỬA THỦ CÔNG**.' . $nl);
        $w('> Bản cập nhật lúc: ' . now()->toIso8601String() . "{$nl}{$nl}");

        $w("## Thông tin chung{$nl}{$nl}");
        $w('- Số bản ghi: **' . number_format($meta['row_count']) . "**{$nl}");
        if ($meta['comment'] !== '') {
            $w('- Comment: ' . $this->md($meta['comment']) . "{$nl}");
        }
        if ($meta['data_file']) {
            $w('- Dữ liệu đầy đủ (JSON Lines, 1 dòng = 1 bản ghi): `' . $meta['data_file'] . "`{$nl}");
        }
        $w("{$nl}");

        $w("## Cấu trúc (columns){$nl}{$nl}");
        $w('| Cột | Kiểu | Null | Mặc định | Extra | Comment |' . $nl);
        $w('|---|---|---|---|---|---|' . $nl);
        foreach ($meta['columns'] as $c) {
            $w(sprintf(
                "| `%s` | %s | %s | %s | %s | %s |{$nl}",
                $c['name'],
                $c['type'],
                $c['nullable'] ? 'YES' : 'NO',
                $c['default'] === null ? '*NULL*' : $this->md($c['default']),
                $c['extra'] !== '' ? $this->md($c['extra']) : '-',
                $c['comment'] !== '' ? $this->md($c['comment']) : '-'
            ));
        }
        $w("{$nl}");

        if (!empty($meta['indexes'])) {
            $w("## Indexes{$nl}{$nl}");
            $w('| Tên | Unique | Kiểu | Cột |' . $nl);
            $w('|---|---|---|---|' . $nl);
            foreach ($meta['indexes'] as $i) {
                $w(sprintf(
                    "| %s | %s | %s | `%s` |{$nl}",
                    $this->md($i['name']),
                    !empty($i['unique']) ? '✔' : '',
                    ($i['type'] ?? '') !== '' ? $this->md($i['type']) : '-',
                    implode('`, `', array_map(fn($c) => $this->md((string) $c), $i['columns'] ?? []))
                ));
            }
            $w("{$nl}");
        }

        if (!empty($meta['foreign_keys'])) {
            $w("## Foreign keys{$nl}{$nl}");
            $w('| Tên | Cột | Tham chiếu |' . $nl);
            $w('|---|---|---|' . $nl);
            foreach ($meta['foreign_keys'] as $f) {
                $w(sprintf(
                    "| %s | `%s` | → `%s` |{$nl}",
                    $this->md($f['name']),
                    $this->md($f['column']),
                    $this->md($f['references'])
                ));
            }
            $w("{$nl}");
        }

        if (!empty($meta['stats'])) {
            $w("## Thống kê dữ liệu{$nl}{$nl}");
            foreach ($meta['stats'] as $col => $s) {
                $parts = [];
                if (array_key_exists('min', $s) || array_key_exists('max', $s)) {
                    $parts[] = sprintf(
                        'min = %s, max = %s',
                        $this->md($s['min'] ?? '-'),
                        $this->md($s['max'] ?? '-')
                    );
                }
                if (!empty($s['top_values'])) {
                    $list = implode(', ', array_map(
                        fn($tv) => sprintf('`%s` (%d)', $this->md($tv['value']), $tv['count']),
                        $s['top_values']
                    ));
                    $parts[] = "giá trị phổ biến: {$list}";
                }
                if ($parts !== []) {
                    $w("- `{$col}`: " . implode(' — ', $parts) . $nl);
                }
            }
            $w("{$nl}");
        }

        $w("## Mẫu dữ liệu{$nl}{$nl}");
        if ($meta['row_count'] === 0) {
            $w('_Bảng trống — chưa có bản ghi nào._' . $nl);
        } else {
            $limit = max(1, min(self::SAMPLE_ROWS, $meta['row_count']));
            $rows = $connection->query()->from($table)->limit($limit)->get();

            $rendered = 0;
            $bytes = 0;
            $headerDone = false;
            $truncated = false;

            foreach ($rows as $row) {
                $row = (array) $row;
                if (!$headerDone) {
                    $head = '| ' . implode(' | ', array_map(fn($c) => $this->md($c), array_keys($row))) . $nl;
                    $sep = '|' . implode('|', array_fill(0, count($row), '---')) . '|' . $nl;
                    $w($head . $sep);
                    $bytes += strlen($head) + strlen($sep);
                    $headerDone = true;
                }

                $line = '| ' . implode(' | ', array_map(fn($v) => $this->cell($v), array_values($row))) . $nl;
                if ($bytes + strlen($line) > self::MAX_TABLE_BYTES) {
                    $truncated = true;
                    break;
                }
                $w($line);
                $bytes += strlen($line);
                $rendered++;
            }

            if ($truncated || $rendered < $meta['row_count']) {
                $w($nl . sprintf(
                    '> Hiển thị %d/%s bản ghi. Toàn bộ dữ liệu nằm trong `%s`.' . $nl,
                    $rendered,
                    number_format($meta['row_count']),
                    $meta['data_file'] ?? 'file dữ liệu'
                ));
            }
        }

        fclose($out);
    }

    private function writeIndexMarkdown(string $dir, string $connName, string $dbName, string $driver, array $manifest): void
    {
        $nl = "\n";
        $totalRows = array_sum(array_column($manifest, 'rows'));

        $md = "# Chỉ mục Database — bản xuất cho AI{$nl}{$nl}";
        $md .= '> Sinh tự động bởi `php artisan ai:export-database`. KHÔNG SỬA THỦ CÔNG — chạy lại lệnh để cập nhật.' . $nl;
        $md .= '- Thời điểm xuất: ' . now()->toIso8601String() . $nl;
        $md .= "- Kết nối: `{$connName}` (driver: `{$driver}`, database: `{$dbName}`)" . $nl;
        $md .= '- Tổng số bảng: ' . count($manifest) . $nl;
        $md .= '- Tổng số bản ghi: ' . number_format($totalRows) . $nl . $nl;
        $md .= '## Cách đọc các file' . $nl . $nl;
        $md .= '- `tables/<tên_bảng>.md`: cấu trúc (cột, kiểu, index, FK), thống kê và MẪU dữ liệu của từng bảng.' . $nl;
        $md .= '- `data/<tên_bảng>.jsonl`: TOÀN BỘ dữ liệu, mỗi dòng là một bản ghi JSON (đọc file này khi cần dữ liệu đầy đủ).' . $nl;
        $md .= '- Nếu cần tái tạo cấu trúc trong code, xem thư mục `database/migrations/`.' . $nl . $nl;
        $md .= '## Danh sách bảng' . $nl . $nl;
        $md .= '| Bảng | Số bản ghi | File cấu trúc + mẫu | File dữ liệu đầy đủ |' . $nl;
        $md .= '|---|---:|---|---|' . $nl;
        foreach ($manifest as $m) {
            $md .= sprintf(
                "| [%s](%s) | %s | %s | %s |\n",
                $m['table'],
                $m['file'],
                number_format($m['rows']),
                $m['file'],
                $m['data'] ?? '-'
            );
        }

        file_put_contents("{$dir}/INDEX.md", $md);
    }

    private function md(mixed $v): string
    {
        $s = $v === null ? 'NULL' : trim(preg_replace('/\s+/u', ' ', (string) $v) ?? '');
        return str_replace(['|', '`', "\n"], ['\\|', "'", ' '], $s);
    }

    private function cell(mixed $v): string
    {
        if ($v === null) {
            return 'NULL';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_array($v)) {
            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
        }
        return $this->md(mb_strcut((string) $v, 0, self::MAX_CELL_LENGTH, 'UTF-8'));
    }
}