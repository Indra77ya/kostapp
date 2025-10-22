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
    Schema::create('rooms', function (Blueprint $t) {
        $t->id();
        $t->foreignId('location_id')->constrained()->cascadeOnDelete();
        $t->foreignId('room_type_id')->constrained()->restrictOnDelete();
        $t->string('number'); // A-101
        $t->enum('status',['available','occupied','maintenance'])->default('available');
        $t->unsignedTinyInteger('floor')->nullable();
        $t->json('amenities')->nullable();
        $t->timestamps();
        $t->unique(['location_id','number']);
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
