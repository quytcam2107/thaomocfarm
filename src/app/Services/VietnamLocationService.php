<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Nạp dữ liệu hành chính 2 cấp (tỉnh/thành + xã/phường) từ JSON vào bảng provinces / wards.
 * Upsert theo khóa tự nhiên (provinces.code, wards.code) → chạy lại nhiều lần an toàn,
 * không tạo bản trùng. Toàn bộ nằm trong MỘT transaction (fail là rollback sạch).
 */
class VietnamLocationService
{
    /** Số bản ghi mỗi câu upsert (giảm số round-trip, tránh SQL quá dài). */
    private const CHUNK_SIZE = 200;

    /**
     * Đọc + validate file JSON.
     *
     * @return list<array<string, mixed>> Mảng tỉnh, mỗi tỉnh có key `wards`
     */
    public function loadProvinces(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("Không tìm thấy file dữ liệu: {$path}");
        }

        // Bỏ BOM UTF-8 nếu có, nếu không json_decode sẽ fail
        $raw = ltrim((string) file_get_contents($path), "\xEF\xBB\xBF");

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(sprintf(
                'File JSON không hợp lệ (%s): %s — kiểm tra file lưu dạng UTF-8 không BOM.',
                $e->getMessage(),
                $path
            ), 0, $e);
        }

        if (!is_array($data) || $data === []) {
            throw new InvalidArgumentException("File JSON rỗng hoặc sai cấu trúc mảng tỉnh: {$path}");
        }

        foreach ($data as $i => $province) {
            $this->assertShape((array) $province, ['code', 'name', 'codename'], "tỉnh thứ #{$i}");

            if (!isset($province['wards']) || !is_array($province['wards'])) {
                throw new InvalidArgumentException("Tỉnh thứ #{$i} thiếu mảng 'wards'.");
            }

            foreach ($province['wards'] as $j => $ward) {
                $this->assertShape(
                    (array) $ward,
                    ['code', 'name', 'codename', 'province_code'],
                    sprintf('phường thứ #%d của tỉnh %s', $j, $province['code'])
                );
            }
        }

        return $data;
    }

    /**
     * Import toàn bộ dữ liệu trong một transaction.
     *
     * @param list<array<string, mixed>> $data  Kết quả loadProvinces()
     * @param bool                       $fresh Xóa sạch 2 bảng trước khi nạp lại
     * @return array{provinces:int,wards:int} Số bản ghi đã ghi nhận
     */
    public function import(array $data, bool $fresh = false): array
    {
        if (DB::transactionLevel() > 0) {
            throw new RuntimeException('import() phải chạy ngoài transaction đang mở.');
        }

        // Chặn sớm khi chưa migrate để báo lỗi dễ hiểu thay vì crash giữa chừng
        if (!Schema::hasTable('provinces') || !Schema::hasTable('wards')) {
            throw new RuntimeException(
                'Bảng provinces/wards chưa tồn tại — hãy chạy `php artisan migrate` trước.'
            );
        }

        $provinceCount = 0;
        $wardCount = 0;

        DB::transaction(function () use ($data, $fresh, &$provinceCount, &$wardCount): void {
            if ($fresh) {
                Ward::query()->delete();      // xóa con trước để không vướng FK
                Province::query()->delete();
            }

            // Một mốc thời gian chung cho cả đợt import (upsert không tự đổ timestamps)
            $now = Carbon::now();

            // 1) Tỉnh — upsert theo code
            foreach (array_chunk($data, self::CHUNK_SIZE) as $chunk) {
                $rows = [];
                foreach ($chunk as $province) {
                    $rows[] = [
                        'code' => (int) $province['code'],
                        'name' => (string) $province['name'],
                        'division_type' => isset($province['division_type']) ? (string) $province['division_type'] : null,
                        'codename' => (string) $province['codename'],
                        'phone_code' => isset($province['phone_code']) ? (int) $province['phone_code'] : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Province::query()->upsert($rows, ['code'], [
                    'name',
                    'division_type',
                    'codename',
                    'phone_code',
                    'updated_at',
                ]);
                $provinceCount += count($rows);
            }

            // 2) Phường — gom của mọi tỉnh rồi upsert theo code (code duy nhất toàn quốc)
            foreach (array_chunk($this->flattenWards($data), self::CHUNK_SIZE) as $chunk) {
                $rows = [];
                foreach ($chunk as $ward) {
                    $rows[] = [
                        'code' => (int) $ward['code'],
                        'name' => (string) $ward['name'],
                        'division_type' => isset($ward['division_type']) ? (string) $ward['division_type'] : null,
                        'codename' => (string) $ward['codename'],
                        'province_code' => (int) $ward['province_code'],
                        'province_name' => isset($ward['province_name']) ? (string) $ward['province_name'] : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Ward::query()->upsert($rows, ['code'], [
                    'name',
                    'division_type',
                    'codename',
                    'province_code',
                    'province_name',
                    'updated_at',
                ]);
                $wardCount += count($rows);
            }

            // 3) FK province_code → provinces.code: migration wards tạo trước provinces
            //    nên không khai báo FK trong migration được; gắn ở đây sau khi dữ liệu đã vào.
            $this->ensureWardProvinceForeignKey();
        });

        return ['provinces' => $provinceCount, 'wards' => $wardCount];
    }

    /** Gắn FK wards.province_code → provinces.code (chỉ MySQL; driver khác bỏ qua). */
    private function ensureWardProvinceForeignKey(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'mysql') {
            return; // SQLite/pgsql: quan hệ app-level (Model relations) vẫn dùng bình thường
        }

        $database = (string) $connection->getConfig('database');

        // Đã có FK rồi thì không thêm lại (lệnh chạy nhiều lần)
        $exists = DB::selectOne(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
            [$database, 'wards', 'fk_wards_province_code']
        );
        if ($exists !== null) {
            return;
        }

        $connection->statement(
            'ALTER TABLE `wards` ADD CONSTRAINT `fk_wards_province_code` '
            . 'FOREIGN KEY (`province_code`) REFERENCES `provinces` (`code`) ON DELETE CASCADE'
        );
    }

    /**
     * Dẹt toàn bộ phường của mọi tỉnh thành một danh sách.
     *
     * @param list<array<string, mixed>> $data
     * @return list<array<string, mixed>>
     */
    private function flattenWards(array $data): array
    {
        $wards = [];

        foreach ($data as $province) {
            foreach ((array) $province['wards'] as $ward) {
                $ward = (array) $ward;
                // fallback lấy thông tin tỉnh cha nếu bản ghi thiếu
                $ward['province_code'] ??= $province['code'];
                $ward['province_name'] ??= $province['name'];
                $wards[] = $ward;
            }
        }

        return $wards;
    }

    /**
     * Kiểm tra một bản ghi có đủ các cột bắt buộc.
     *
     * @param array<string, mixed> $row
     * @param list<string>         $required
     * @param string               $label Nhãn phục vụ thông báo lỗi
     */
    private function assertShape(array $row, array $required, string $label): void
    {
        foreach ($required as $key) {
            if (!array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
                throw new InvalidArgumentException("Thiếu cột bắt buộc '{$key}' ở {$label}.");
            }
        }
    }
}