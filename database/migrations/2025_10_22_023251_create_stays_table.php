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
    Schema::create('stays', function (Blueprint $t) {
        $t->id();
        $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $t->foreignId('room_id')->constrained()->restrictOnDelete();
        $t->date('checkin_date');
        $t->date('checkout_date')->nullable();
        $t->enum('billing_cycle',['daily','monthly'])->default('monthly');
        $t->decimal('rate',12,2);
        $t->enum('status',['active','ended'])->default('active');
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};
