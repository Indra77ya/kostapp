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
    Schema::create('room_types', function (Blueprint $t) {
        $t->id();
        $t->string('name'); // Standard/Deluxe/Homestay
        $t->enum('billing_cycle',['daily','monthly'])->default('monthly');
        $t->decimal('base_price',12,2)->default(0);
        $t->timestamps();
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
