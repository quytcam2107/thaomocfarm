<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedTinyInteger('rating');
            $table->text('content');
            $table->json('images')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('admin_reply')->nullable();
            $table->enum('status', ['pending', 'approved', 'hidden'])->default('pending');
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
            $table->index(['product_id', 'status', 'created_at']);
            $table->index(['product_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};