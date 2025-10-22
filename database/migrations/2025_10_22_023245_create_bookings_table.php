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
    Schema::create('bookings', function (Blueprint $t) {
        $t->id();
        $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $t->foreignId('room_id')->constrained()->restrictOnDelete();
        $t->date('start_date');
        $t->date('end_date')->nullable(); // wajib untuk homestay (harian)
        $t->enum('status',['pending','confirmed','cancelled'])->default('pending');
        $t->decimal('rate',12,2);
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
