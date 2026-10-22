
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus indeks unik username terlebih dahulu
        Schema::table('users', function ($table) {
            $table->dropUnique('users_username_unique');
        });

        // Setelah indeks dihapus, hapus kolom username
        Schema::table('users', function ($table) {
            $table->dropColumn('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->string('username')->nullable()->unique();
        });
    }
};
