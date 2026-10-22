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
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_week_id')->constrained('course_weeks')->onDelete('cascade');
            $table->enum('type', ['video', 'pdf', 'quiz']); // Tipe materi
            $table->string('title');
            $table->string('content_url')->nullable(); // Link video atau nama file PDF
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_materials');
    }
};
