<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uid')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->boolean('liked');
            $table->timestamps();
            $table->unique(['uid', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_swipes');
    }
};
