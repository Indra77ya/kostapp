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
            $table->decimal('price_daily', 15, 2)->nullable()->after('price');
            $table->decimal('price_weekly', 15, 2)->nullable()->after('price_daily');
            $table->decimal('price_yearly', 15, 2)->nullable()->after('price'); // existing 'price' is monthly
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_daily', 'price_weekly', 'price_yearly']);
        });
    }
};
