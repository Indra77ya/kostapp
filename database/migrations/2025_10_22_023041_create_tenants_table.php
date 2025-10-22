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
    Schema::create('tenants', function (Blueprint $t) {
        $t->id();
        $t->string('full_name');
        $t->string('phone')->nullable();
        $t->string('email')->nullable();
        $t->string('national_id')->nullable(); // NIK
        $t->text('notes')->nullable();
        $t->timestamps();
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
