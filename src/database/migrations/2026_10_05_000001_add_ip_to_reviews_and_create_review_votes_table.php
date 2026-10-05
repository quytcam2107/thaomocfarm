<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Bổ sung hạ tầng đánh giá sản phẩm (PDP):
 * - reviews.ip_address: định danh khách vãng lai (chưa có auth UI) — chặn
 *   1 IP ghi nhiều review cho cùng 1 sản phẩm; nullable, KHÔNG phá dữ liệu cũ.
 * - review_votes: lưu lượt "Hữu ích" (duy nhất review_id + ip_address),
 *   không FK constraint theo convention dự án (toàn vẹn do Service đảm bảo).
 */
return new class extends Migration {
    public function up(): void
    {
        // Cột mới cho SQLite/MySQL dev: kiểm tra tồn tại trước (idempotent)
        if (!Schema::hasColumn('reviews', 'ip_address')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->string('ip_address', 45)->nullable()->after('user_id');
            });
        }

        Schema::create('review_votes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('review_id')->index();
            $table->string('ip_address', 45);
            $table->timestamps();
            // 1 IP chỉ "Hữu ích" 1 lần cho 1 review
            $table->unique(['review_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_votes');

        if (Schema::hasColumn('reviews', 'ip_address')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn('ip_address');
            });
        }
    }
};