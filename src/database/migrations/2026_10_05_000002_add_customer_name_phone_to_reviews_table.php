<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Đối chiếu "đã mua hay chưa" bằng SỐ ĐIỆN THOẠI (yêu cầu mới):
 * - reviews.user_id NOT NULL (migration gốc 000023) chặn khách vãng lai —
 *   nới thành nullable để form PDP gửi được dù chưa đăng nhập (định danh
 *   thật là SĐT + IP). Đã có migration 000001 thêm ip_address/customer_*
 *   tương tự theo convention idempotent hasColumn() của dự án.
 * - reviews.customer_name: tên khách tự nhập — hiển thị trên PDP.
 * - reviews.customer_phone: SĐT dùng khi đặt hàng (orders.customer_phone),
 *   nguồn đối chiếu đơn đã giao thay cho email. Email KHÔNG thêm cột riêng
 *   vì cột customer_email trong bảng reviews CHƯA tồn tại (xem
 *   ai-database/tables/reviews.md) — service chỉ dùng email làm tham chiếu
 *   phụ lúc kiểm tra đơn qua orders.customer_email.
 * Cột nullable + kiểm tra tồn tại trước (idempotent) → không phá dữ liệu cũ.
 *
 * FIX LỖI 1054 "Unknown column 'customer_name'": migration NÀY chưa được
 * chạy trên DB local (bản xuất ai-database/tables/reviews.md lập ngày
 * 05/10/2026 11:13Z — TRƯỚC commit "Update Feature Review" — vẫn chỉ có
 * các cột gốc + ip_address). Code ReviewService/Review/ProductDetailFetcher
 * đã ghi/đọc customer_name/customer_phone → INSERT fail. Cách xử lý:
 *   cd src && php artisan migrate
 * Đồng thời guard CẢ phần change() user_id (trước đây chạy vô điều kiện,
 * migrate lại 2 lần sẽ throw) — migration giờ idempotent hoàn toàn.
 */
return new class extends Migration {
    public function up(): void
    {
        // 1) Nới user_id thành nullable NHƯNG chỉ khi cột còn NOT NULL
        //    (đọc cấu trúc THẬT qua getColumns() — MySQL/SQLite đều hỗ trợ;
        //    tránh lỗi/làm vô ích khi migration đã chạy một lần trước đó)
        $userCol = collect(Schema::getColumns('reviews'))
            ->first(fn(array $c): bool => $c['name'] === 'user_id');

        if ($userCol !== null && $userCol['nullable'] !== true) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }

        // 2) customer_name — cột mới mà code ReviewService::store() đang INSERT
        if (!Schema::hasColumn('reviews', 'customer_name')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->string('customer_name', 100)->nullable()->after('ip_address');
            });
        }

        // 3) customer_phone — nguồn đối chiếu đơn delivered (ReviewPhoneMatcher)
        if (!Schema::hasColumn('reviews', 'customer_phone')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->string('customer_phone', 20)->nullable()->after('customer_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reviews', 'customer_phone')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn('customer_phone');
            });
        }

        if (Schema::hasColumn('reviews', 'customer_name')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn('customer_name');
            });
        }

        // Chỉ khôi phục NOT NULL nếu không còn review guest (an toàn dữ liệu)
        if (!DB::table('reviews')->whereNull('user_id')->exists()) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};