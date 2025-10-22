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
    Schema::create('invoices', function (Blueprint $t) {
        $t->id();
        $t->string('number')->unique();
        $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $t->foreignId('stay_id')->nullable()->constrained()->nullOnDelete();
        $t->date('issue_date');
        $t->date('due_date');
        $t->decimal('subtotal',12,2);
        $t->decimal('discount',12,2)->default(0);
        $t->decimal('tax',12,2)->default(0);
        $t->decimal('total',12,2);
        $t->enum('status',['unpaid','partial','paid','overdue'])->default('unpaid');
        $t->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
