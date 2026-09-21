<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_snapshot');
            $table->string('sku_snapshot');
            $table->string('image_snapshot')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('qty');
            $table->unsignedInteger('subtotal');
            $table->timestamps();
            $table->index(['order_id']);
            $table->index(['product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('order_items'); }
};