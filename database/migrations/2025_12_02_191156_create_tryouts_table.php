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
        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->string('title');       // Contoh: "Tryout Biologi: Sistem Peredaran Darah"
            $table->string('subject');     // Contoh: "Biologi"
            $table->integer('duration');   // Durasi dalam menit (misal: 30)
            $table->integer('total_questions')->default(0);
            $table->enum('status', ['active', 'upcoming', 'finished'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryouts');
    }
};
