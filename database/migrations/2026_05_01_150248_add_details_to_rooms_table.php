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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('image')->nullable()->after('status');
            $table->text('facilities')->nullable()->after('image');
            $table->string('room_type')->nullable()->after('facilities');
            $table->string('floor')->nullable()->after('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['image', 'facilities', 'room_type', 'floor']);
        });
    }
};
