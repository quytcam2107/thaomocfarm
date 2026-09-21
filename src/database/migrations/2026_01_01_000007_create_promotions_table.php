<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['flash_sale', 'campaign']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->enum('status', ['scheduled', 'active', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamps();
            $table->index(['status', 'start_at', 'end_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('promotions'); }
};