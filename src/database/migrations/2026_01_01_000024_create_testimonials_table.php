<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_location')->nullable();
            $table->unsignedSmallInteger('orders_count')->default(1);
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('content');
            $table->string('avatar')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'hidden'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};