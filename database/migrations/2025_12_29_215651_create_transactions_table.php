<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('order_id')->unique();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null'); 
        $table->decimal('amount', 15, 2);
        $table->string('payment_method')->nullable(); // Sesuaikan nama kolom di Controller ('payment_method' vs 'payment_type')
        $table->string('status')->default('pending');
        $table->string('snap_token')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
