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
    Schema::create('journal_lines', function (Blueprint $t) {
        $t->id();
        $t->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
        $t->foreignId('account_id')->constrained()->restrictOnDelete();
        $t->decimal('debit',12,2)->default(0);
        $t->decimal('credit',12,2)->default(0);
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
