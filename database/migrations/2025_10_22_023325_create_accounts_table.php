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
    Schema::create('accounts', function (Blueprint $t) {
        $t->id();
        $t->string('code')->unique(); // 1100 Kas, 4100 Pendapatan, dll.
        $t->string('name');
        $t->enum('type',['asset','liability','equity','revenue','expense']);
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
