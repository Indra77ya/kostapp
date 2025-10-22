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
    Schema::create('locations', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('code')->unique();          // contoh: KOST-A, HMS-1
        $t->string('address')->nullable();
        $t->enum('type',['kost','homestay'])->default('kost');
        $t->unsignedInteger('default_room_quota')->default(40);
        $t->string('wa_group_link')->nullable();
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
