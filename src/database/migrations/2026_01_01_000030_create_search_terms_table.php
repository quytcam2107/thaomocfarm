<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term')->unique();
            $table->unsignedInteger('hits')->default(1);
            $table->timestamps();
            $table->index(['hits']);
        });
    }
    public function down(): void { Schema::dropIfExists('search_terms'); }
};