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
        Schema::create('locations', function (Blueprint $create) {
            $create->id();
            $create->string('name');
            $create->text('address')->nullable();
            $create->string('google_maps_link')->nullable();
            $create->string('phone')->nullable();
            $create->text('description')->nullable();
            $create->string('image')->nullable();
            $create->timestamps();
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
