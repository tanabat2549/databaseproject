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
        Schema::create('user_phones', function (Blueprint $table) {
            $table->foreignId('uid')->constrained('users')->cascadeOnDelete();
            $table->string('phone_no', 20);
            $table->timestamps();
            $table->primary(['uid', 'phone_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_phones');
    }
};
