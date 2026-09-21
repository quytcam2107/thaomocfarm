<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('flash_price');
            $table->unsignedSmallInteger('discount_percent')->default(0);
            $table->unsignedInteger('qty_total');
            $table->unsignedInteger('qty_sold')->default(0);
            $table->unsignedSmallInteger('per_user_limit')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['promotion_id', 'sort_order']);
            $table->index(['product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('promotion_products'); }
};