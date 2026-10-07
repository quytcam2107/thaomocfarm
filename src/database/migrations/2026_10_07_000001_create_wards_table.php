<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng xã/phường (cấp hành chính 2) — nguồn dữ liệu data/vietnam_2_levels_v2.json.
 * Migration này chạy TRƯỚC create_provinces_table, nên FK province_code
 * chỉ được gắn index thường; FK thật do lệnh `php artisan admin:import-locations`
 * thêm sau khi bảng provinces đã có dữ liệu (xem VietnamLocationService).
 *
 * ⚠️ DỮ LIỆU THẬT: codename phường BỊ TRÙNG giữa các tỉnh (293 codename trùng,
 * VD xa_lien_minh xuất hiện 3 lần) → KHÔNG đặt unique cho codename/name,
 * chỉ `code` là duy nhất toàn quốc.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->id();                                        // id tự tăng, dùng nội bộ
            $table->unsignedInteger('code')->unique();           // mã xã/phường theo quyết định (VD: 4 = Phường Ba Đình)
            $table->string('name', 100);                         // "Phường Ba Đình" (không unique — trùng tên giữa các tỉnh)
            $table->string('division_type', 50)->nullable();     // "phường", "xã", "đặc khu"
            $table->string('codename', 100)->index();            // "phuong_ba_dinh" (KHÔNG unique — trùng giữa các tỉnh)
            $table->unsignedInteger('province_code')->index();   // mã tỉnh cha (tham chiếu provinces.code)
            $table->string('province_name', 100)->nullable();    // tên tỉnh cha — giữ nguyên từ JSON để đối chiếu
            $table->timestamps();

            $table->index(['province_code', 'name']);            // kéo danh sách phường theo tỉnh
            $table->index(['province_code', 'codename']);        // tra cứu nhanh trong phạm vi 1 tỉnh
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};