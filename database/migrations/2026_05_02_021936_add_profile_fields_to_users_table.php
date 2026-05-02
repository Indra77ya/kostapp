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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone_number')->nullable()->after('avatar');
            $table->text('address')->nullable()->after('phone_number');
            $table->text('bank_info')->nullable()->after('address');
            $table->text('bio')->nullable()->after('bank_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'phone_number', 'address', 'bank_info', 'bio']);
        });
    }
};
