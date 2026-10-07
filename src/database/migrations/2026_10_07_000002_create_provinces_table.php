<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng tỉnh/thành phố (cấp hành chính 1) — nguồn dữ liệu data/vietnam_2_levels_v2.json.
 * Timestamp lớn hơn migration wards để bảng con tạo trước, bảng cha tạo sau;
 * FK wards.province_code → provinces.code được service gắn sau khi import xong dữ liệu.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();                                          // id tự tăng, dùng nội bộ
            $table->unsignedInteger('code')->unique();             // mã tỉnh theo quyết định (VD: 1 = Hà Nội)
            $table->string('name', 100);                           // "Thành phố Hà Nội"
            $table->string('division_type', 50)->nullable();       // "thành phố trung ương", "tỉnh"
            $table->string('codename', 100)->unique();             // "ha_noi" (34 codename đều duy nhất)
            $table->unsignedTinyInteger('phone_code')->nullable(); // mã điện thoại (24, 28...)
            $table->timestamps();

            $table->index('name');                                 // tìm theo tên khi đối chiếu địa chỉ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};