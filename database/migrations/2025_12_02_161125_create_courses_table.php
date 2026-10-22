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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('title');        // Nama Kelas
        $table->string('mentor');       // Nama Mentor
        $table->text('description');    // Deskripsi Singkat
        $table->string('schedule');     // Jadwal (Misal: Senin, 14.00)
        $table->string('image')->nullable(); // Foto Banner Kelas
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
