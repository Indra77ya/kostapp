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
        Schema::create('bookings', function (Blueprint $col) {
            $col->id();
            $col->foreignId('user_id')->constrained()->onDelete('cascade');
            $col->foreignId('room_id')->constrained()->onDelete('cascade');
            $col->date('check_in');
            $col->date('check_out')->nullable();
            $col->decimal('total_price', 12, 2);
            $col->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $col->timestamps();
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
