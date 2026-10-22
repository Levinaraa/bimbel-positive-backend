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
        Schema::table('courses', function (Blueprint $table) {
            // Menambahkan kolom baru setelah kolom 'image' agar rapi
            $table->string('jenjang', 10)->nullable()->after('image');  // Contoh: SD, SMP, SMA, UTBK
            $table->integer('tingkat')->nullable()->after('jenjang');     // Contoh: 10, 11, 12
            $table->integer('kapasitas')->nullable()->after('tingkat');   // Contoh: 30 (siswa)
            $table->integer('durasi')->nullable()->after('kapasitas');    // Contoh: 90 (menit)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn(['jenjang', 'tingkat', 'kapasitas', 'durasi']);
        });
    }
};
