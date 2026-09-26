<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Thêm cột coupon_code_snapshot vào bảng orders.
     * Lưu mã giảm giá tại thời điểm đặt hàng (để audit sau này).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'coupon_code_snapshot')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('coupon_code_snapshot', 50)
                    ->nullable()
                    ->after('coupon_id');
            });
        }
    }

    /**
     * Rollback: xóa cột coupon_code_snapshot.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'coupon_code_snapshot')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('coupon_code_snapshot');
            });
        }
    }
};