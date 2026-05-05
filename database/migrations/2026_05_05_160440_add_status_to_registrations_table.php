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
        Schema::table('registrations', function (Blueprint $row) {
            $row->string('status')->default('active')->after('total_price');
            $row->date('check_out_date')->nullable()->after('stay_start_date');
            $row->text('check_out_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $row) {
            $row->dropColumn(['status', 'check_out_date', 'check_out_notes']);
        });
    }
};
