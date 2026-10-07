<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\VietnamLocationService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Import danh sách 34 tỉnh/thành + 3.321 xã/phường (địa chính 2 cấp áp dụng từ 01/07/2025)
 * từ file src/data/vietnam_2_levels_v2.json vào bảng `provinces` và `wards`.
 * Phạm vi: CHỈ nạp dữ liệu — chưa gắn vào form địa chỉ/checkout (làm ở yêu cầu sau).
 */
class ImportVietnamLocations extends Command
{
    protected $signature = 'admin:import-locations
                            {--path= : Đường dẫn file JSON (mặc định: data/vietnam_2_levels_v2.json trong thư mục app)}
                            {--fresh : Xóa toàn bộ dữ liệu cũ của provinces + wards trước khi nạp lại}
                            {--dry-run : Chỉ đọc + kiểm tra dữ liệu, không ghi database}';

    protected $description = 'Import dữ liệu hành chính 2 cấp (tỉnh/thành + xã/phường) vào bảng provinces và wards';

    public function handle(VietnamLocationService $service): int
    {
        // base_path() = thư mục src/ → file chuẩn nằm ở src/data/
        $path = (string) ($this->option('path') ?: base_path('data/vietnam_2_levels_v2.json'));

        $this->info('Nguồn dữ liệu : ' . $path);

        try {
            $data = $service->loadProvinces($path);
        } catch (Throwable $e) {
            $this->error('Không đọc được file dữ liệu: ' . $e->getMessage());

            return self::FAILURE;
        }

        $wardTotal = array_sum(
            array_map(static fn(array $p): int => count((array) ($p['wards'] ?? [])), $data)
        );
        $this->info(sprintf('Dữ liệu hợp lệ: %d tỉnh/thành, %d xã/phường.', count($data), $wardTotal));

        if ($this->option('dry-run')) {
            $this->warn('--dry-run: không ghi database.');

            return self::SUCCESS;
        }

        $this->info('Đang import...');

        try {
            $result = $service->import($data, (bool) $this->option('fresh'));
        } catch (Throwable $e) {
            $this->error('Import thất bại (đã rollback, database giữ nguyên): ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Bảng', 'Số bản ghi đã ghi nhận'],
            [
                ['provinces', $result['provinces']],
                ['wards', $result['wards']],
            ]
        );

        $this->info('Hoàn tất. Chạy `php artisan ai:export-database` để cập nhật bản xuất PROJECT_CONTEXT.');

        return self::SUCCESS;
    }
}