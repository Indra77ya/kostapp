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
        Schema::create('utility_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., Listrik, Air
            $table->string('unit')->default('kWh'); // e.g., kWh, m3
            $table->decimal('rate_per_unit', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('utility_type_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('usage_amount', 12, 2)->default(0);
            $table->decimal('rate_per_unit', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->string('status')->default('draft'); // draft, billed
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
        Schema::dropIfExists('utility_types');
    }
};
