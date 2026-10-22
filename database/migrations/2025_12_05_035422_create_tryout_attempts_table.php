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
        Schema::create('tryout_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tryout_id')->constrained('tryouts')->onDelete('cascade');
            $table->integer('total_correct'); // Jumlah Benar
            $table->integer('total_wrong');   // Jumlah Salah
            $table->integer('score');         // Nilai Akhir (0-100)
            $table->timestamps(); // Mencatat tanggal mengerjakan (created_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryout_attempts');
    }
};
