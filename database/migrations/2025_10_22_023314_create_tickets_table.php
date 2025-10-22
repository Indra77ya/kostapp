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
    Schema::create('tickets', function (Blueprint $t) {
        $t->id();
        $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
        $t->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
        $t->string('subject');
        $t->text('description')->nullable();
        $t->enum('priority',['low','medium','high'])->default('low');
        $t->enum('status',['open','in_progress','resolved','closed'])->default('open');
        $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
