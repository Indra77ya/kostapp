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
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'purchase_source_type')) {
                $table->string('purchase_source_type')->default('cash')->after('purchase_cost');
            }
            if (!Schema::hasColumn('assets', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->after('purchase_source_type')->constrained('payment_methods')->nullOnDelete();
            }
            if (!Schema::hasColumn('assets', 'purchase_journal_entry_id')) {
                $table->foreignId('purchase_journal_entry_id')->nullable()->after('payment_method_id')->constrained('journal_entries')->nullOnDelete();
            }
            if (!Schema::hasColumn('assets', 'chart_of_account_id')) {
                $table->foreignId('chart_of_account_id')->nullable()->after('salvage_value')->constrained('chart_of_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('assets', 'accumulated_depreciation_account_id')) {
                $table->foreignId('accumulated_depreciation_account_id')->nullable()->after('chart_of_account_id')->constrained('chart_of_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('assets', 'depreciation_expense_account_id')) {
                $table->foreignId('depreciation_expense_account_id')->nullable()->after('accumulated_depreciation_account_id')->constrained('chart_of_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'depreciation_expense_account_id')) {
                $table->dropForeign(['depreciation_expense_account_id']);
                $table->dropColumn('depreciation_expense_account_id');
            }
            if (Schema::hasColumn('assets', 'accumulated_depreciation_account_id')) {
                $table->dropForeign(['accumulated_depreciation_account_id']);
                $table->dropColumn('accumulated_depreciation_account_id');
            }
            if (Schema::hasColumn('assets', 'chart_of_account_id')) {
                $table->dropForeign(['chart_of_account_id']);
                $table->dropColumn('chart_of_account_id');
            }
            if (Schema::hasColumn('assets', 'purchase_journal_entry_id')) {
                $table->dropForeign(['purchase_journal_entry_id']);
                $table->dropColumn('purchase_journal_entry_id');
            }
            if (Schema::hasColumn('assets', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }
            if (Schema::hasColumn('assets', 'purchase_source_type')) {
                $table->dropColumn('purchase_source_type');
            }
        });
    }
};
