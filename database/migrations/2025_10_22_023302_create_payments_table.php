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
    Schema::create('payments', function (Blueprint $t) {
        $t->id();
        $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
        $t->date('paid_at');
        $t->decimal('amount',12,2);
        $t->string('method')->nullable();    // cash/transfer/qris
        $t->string('reference')->nullable(); // no. transaksi bank
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
