<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index(); // không FK
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('price_min')->default(0);
            $table->unsignedInteger('compare_price')->default(0);
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'active', 'hidden'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->index(['status', 'is_featured', 'published_at']);
            $table->index(['status', 'sold_count']);
            $table->index(['category_id', 'status', 'price_min']);
        });
        DB::statement('ALTER TABLE products ADD FULLTEXT search_name (name)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};