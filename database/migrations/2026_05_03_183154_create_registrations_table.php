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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');

            $table->string('registration_number')->unique();
            $table->date('registration_date');
            $table->date('stay_start_date');

            // Financials
            $table->decimal('room_price', 15, 2);
            $table->string('discount_type')->nullable(); // 'percent' or 'fixed'
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2);

            // Personal Info
            $table->string('identity_type'); // KTP, SIM, etc.
            $table->string('identity_number');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('birth_place');
            $table->date('birth_date');

            // Photos
            $table->string('photo_self')->nullable();
            $table->string('photo_identity')->nullable();
            $table->string('family_card_number')->nullable();
            $table->string('photo_family_card')->nullable();

            // Organization Info
            $table->string('institution_name')->nullable();
            $table->text('institution_address')->nullable();
            $table->string('institution_phone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
