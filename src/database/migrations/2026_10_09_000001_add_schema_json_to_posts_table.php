<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột schema_json vào bảng posts: lưu JSON-LD (FAQPage, HowTo...) cho bài viết.
 * Tách khỏi `content` để:
 *  - render_cms_html() sanitize whitelist tag KHÔNG nuốt <script> ld+json trong content;
 *  - view blog/show đọc cột riêng, validate JSON rồi xuất thẳng vào <head> (slot $schema).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            // longText nullable — đứng NGAY sau content cho dễ nhìn khi quản trị DB
            $table->longText('schema_json')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('schema_json');
        });
    }
};
