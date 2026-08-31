<?php

namespace App\Services;

use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\FundTransfer;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Get ChartOfAccount model by code.
     */
    public static function getAccountByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }

    /**
     * Helper to get account ID by mapping key with fallback to standard code.
     */
    public static function getAccountIdByMapping(string $mappingKey, string $defaultCode): ?int
    {
        $id = AccountMapping::getAccountId($mappingKey);
        if ($id) {
            return $id;
        }

        $account = self::getAccountByCode($defaultCode);
        return $account?->id;
    }

    /**
     * Generate unique journal entry number.
     */
    public static function generateJournalNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "JRN-{$dateStr}-";

        $lastEntry = JournalEntry::where('entry_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEntry) {
            $lastSeq = (int) substr($lastEntry->entry_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Record a general double-entry journal.
     */
    public static function createJournalEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $journal = JournalEntry::create([
                'entry_number' => self::generateJournalNumber(),
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'description' => $data['description'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'memo' => $item['memo'] ?? null,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Record journal entry for confirmed payments.
     */
    public static function recordPaymentJournal(Payment $payment): ?JournalEntry
    {
        // Avoid duplicate journal entries for the same payment
        $existing = JournalEntry::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $pm = $payment->paymentMethod;
        $cashAccountId = ($pm && $pm->chart_of_account_id)
            ? $pm->chart_of_account_id
            : self::getAccountIdByMapping('default_cash', '1-1000');

        $depositLiabilityAccountId = self::getAccountIdByMapping('deposit_liability', '2-1000');
        $rentalRevenueAccountId = self::getAccountIdByMapping('rental_revenue', '4-1000');

        if (!$cashAccountId || !$depositLiabilityAccountId || !$rentalRevenueAccountId) {
            return null;
        }

        $cashAccount = ChartOfAccount::find($cashAccountId);
        if (!$cashAccount) {
            return null;
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            return null;
        }

        $items = [];
        $description = "Pembayaran " . ($payment->payment_number ?? "#{$payment->id}");

        // Case 1: Payment using Saldo Deposit
        if ($payment->paymentMethod && strtolower($payment->paymentMethod->name) === 'saldo deposit') {
            // Debit: Deposit Liability (Reducing deposit liability)
            // Credit: Rental Revenue
            $items[] = [
                'chart_of_account_id' => $depositLiabilityAccountId,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Penggunaan Saldo Deposit untuk Tagihan',
            ];
            $items[] = [
                'chart_of_account_id' => $rentalRevenueAccountId,
                'debit' => 0,
                'credit' => $amount,
                'memo' => 'Pendapatan Sewa dari Deposit',
            ];

            return self::createJournalEntry([
                'entry_date' => $payment->payment_date ? $payment->payment_date->toDateString() : now()->toDateString(),
                'description' => "Penggunaan Deposit: " . $description,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'created_by' => Auth::id(),
                'items' => $items,
            ]);
        }

        // Case 2: Setor Deposit
        if (str_contains($payment->notes ?? '', '[DEPOSIT]') || ($payment->bill && str_contains($payment->bill->description, 'Deposit Awal'))) {
            // Debit: Cash & Bank
            // Credit: Deposit Liability
            $items[] = [
                'chart_of_account_id' => $cashAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Penerimaan Kas Setoran Deposit',
            ];
            $items[] = [
                'chart_of_account_id' => $depositLiabilityAccountId,
                'debit' => 0,
                'credit' => $amount,
                'memo' => 'Titipan Deposit Tenant',
            ];

            return self::createJournalEntry([
                'entry_date' => $payment->payment_date ? $payment->payment_date->toDateString() : now()->toDateString(),
                'description' => "Setoran Deposit: " . $description,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'created_by' => Auth::id(),
                'items' => $items,
            ]);
        }

        // Case 3: Bill Payment or General Payment (with possible overpayment split)
        // Check if there is an overpayment that went to deposit
        $revenueAmount = $amount;
        $excessAmount = 0;

        if ($payment->bill) {
            $bill = $payment->bill;
            // Get previous confirmed payments for this bill excluding current payment
            $prevPaid = Payment::where('bill_id', $bill->id)
                ->where('id', '!=', $payment->id)
                ->where('status', 'Dikonfirmasi')
                ->sum('amount');

            $billRemaining = max(0, $bill->amount - $prevPaid);
            if ($amount > $billRemaining && $billRemaining >= 0) {
                $revenueAmount = $billRemaining;
                $excessAmount = $amount - $billRemaining;
            }
        }

        // Debit Cash
        $items[] = [
            'chart_of_account_id' => $cashAccount->id,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Penerimaan Kas Pembayaran Sewa',
        ];

        if ($revenueAmount > 0) {
            $items[] = [
                'chart_of_account_id' => $rentalRevenueAccountId,
                'debit' => 0,
                'credit' => $revenueAmount,
                'memo' => 'Pendapatan Sewa Kamar',
            ];
        }

        if ($excessAmount > 0) {
            $items[] = [
                'chart_of_account_id' => $depositLiabilityAccountId,
                'debit' => 0,
                'credit' => $excessAmount,
                'memo' => 'Kelebihan Bayar masuk ke Deposit',
            ];
        }

        return self::createJournalEntry([
            'entry_date' => $payment->payment_date ? $payment->payment_date->toDateString() : now()->toDateString(),
            'description' => $description,
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'created_by' => Auth::id(),
            'items' => $items,
        ]);
    }

    /**
     * Record journal entry for expenses.
     */
    public static function recordExpenseJournal(Expense $expense): ?JournalEntry
    {
        $existing = JournalEntry::where('reference_type', Expense::class)
            ->where('reference_id', $expense->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $pm = $expense->paymentMethod;
        $cashAccountId = ($pm && $pm->chart_of_account_id)
            ? $pm->chart_of_account_id
            : self::getAccountIdByMapping('default_cash', '1-1000');

        if (!$cashAccountId) {
            return null;
        }

        $items = [
            [
                'chart_of_account_id' => $expense->chart_of_account_id,
                'debit' => $expense->amount,
                'credit' => 0,
                'memo' => $expense->title,
            ],
            [
                'chart_of_account_id' => $cashAccountId,
                'debit' => 0,
                'credit' => $expense->amount,
                'memo' => 'Pengeluaran Kas: ' . $expense->title,
            ],
        ];

        return self::createJournalEntry([
            'entry_date' => $expense->expense_date->toDateString(),
            'description' => "Pengeluaran: " . $expense->title,
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
            'created_by' => $expense->created_by ?? Auth::id(),
            'items' => $items,
        ]);
    }

    /**
     * Record journal entry for Check Out deposit deduction & refund.
     */
    public static function recordCheckOutJournal(Registration $registration, float $deductionAmount, float $refundAmount): ?JournalEntry
    {
        if ($deductionAmount <= 0 && $refundAmount <= 0) {
            return null;
        }

        $depositLiabilityAccountId = self::getAccountIdByMapping('deposit_liability', '2-1000');
        $claimRevenueAccountId = self::getAccountIdByMapping('damage_claim_revenue', '4-3000');
        $cashAccountId = self::getAccountIdByMapping('default_cash', '1-1000');

        if (!$depositLiabilityAccountId) {
            return null;
        }

        $items = [];
        $totalDepositCleared = $deductionAmount + $refundAmount;

        // Debit: Utang Deposit Tenant (reduce total liability)
        $items[] = [
            'chart_of_account_id' => $depositLiabilityAccountId,
            'debit' => $totalDepositCleared,
            'credit' => 0,
            'memo' => 'Penyelesaian Deposit Check Out ' . optional($registration->user)->name,
        ];

        // Credit: Claim revenue for deduction
        if ($deductionAmount > 0 && $claimRevenueAccountId) {
            $items[] = [
                'chart_of_account_id' => $claimRevenueAccountId,
                'debit' => 0,
                'credit' => $deductionAmount,
                'memo' => 'Pendapatan Potongan Kerusakan Deposit',
            ];
        }

        // Credit: Cash for refund
        if ($refundAmount > 0 && $cashAccountId) {
            $items[] = [
                'chart_of_account_id' => $cashAccountId,
                'debit' => 0,
                'credit' => $refundAmount,
                'memo' => 'Pengembalian Deposit (Refund) ke Tenant',
            ];
        }

        return self::createJournalEntry([
            'entry_date' => now()->toDateString(),
            'description' => "Penyelesaian Deposit Check Out: " . optional($registration->user)->name,
            'reference_type' => Registration::class,
            'reference_id' => $registration->id,
            'created_by' => Auth::id(),
            'items' => $items,
        ]);
    }

    /**
     * Record journal entry for fund transfer between accounts.
     */
    public static function recordTransferJournal(FundTransfer $transfer): ?JournalEntry
    {
        $existing = JournalEntry::where('reference_type', FundTransfer::class)
            ->where('reference_id', $transfer->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $amount = (float) $transfer->amount;
        $adminFee = (float) $transfer->admin_fee;

        if ($amount <= 0) {
            return null;
        }

        $items = [];

        // 1. Debit: Destination Account (Receiving funds)
        $items[] = [
            'chart_of_account_id' => $transfer->to_account_id,
            'debit' => $amount,
            'credit' => 0,
            'memo' => "Transfer Masuk ({$transfer->transfer_number})",
        ];

        // 2. Debit: Admin Fee Account (if admin_fee > 0)
        if ($adminFee > 0) {
            $adminFeeAccountId = $transfer->admin_fee_account_id
                ?? self::getAccountIdByMapping('bank_admin_fee', '5-6100');

            if ($adminFeeAccountId) {
                $items[] = [
                    'chart_of_account_id' => $adminFeeAccountId,
                    'debit' => $adminFee,
                    'credit' => 0,
                    'memo' => "Biaya Admin Transfer ({$transfer->transfer_number})",
                ];
            }
        }

        // 3. Credit: Source Account (Transfer amount + admin fee)
        $totalOutgoing = $amount + $adminFee;
        $items[] = [
            'chart_of_account_id' => $transfer->from_account_id,
            'debit' => 0,
            'credit' => $totalOutgoing,
            'memo' => "Transfer Keluar ({$transfer->transfer_number})",
        ];

        $description = "Transfer Dana " . $transfer->transfer_number . ($transfer->notes ? ": {$transfer->notes}" : "");

        return self::createJournalEntry([
            'entry_date' => $transfer->transfer_date ? $transfer->transfer_date->toDateString() : now()->toDateString(),
            'description' => $description,
            'reference_type' => FundTransfer::class,
            'reference_id' => $transfer->id,
            'created_by' => $transfer->created_by ?? Auth::id(),
            'items' => $items,
        ]);
    }

    /**
     * Record journal entry for Asset Purchase / Capitalization.
     */
    public static function recordAssetPurchaseJournal(Asset $asset): ?JournalEntry
    {
        // Existing purchase journal check or return
        if ($asset->purchase_journal_entry_id) {
            $existing = JournalEntry::find($asset->purchase_journal_entry_id);
            if ($existing) {
                return $existing;
            }
        }

        $cost = (float) $asset->purchase_cost;
        if ($cost <= 0) {
            return null;
        }

        // 1. If purchase_source_type is 'existing', do not create accounting journal
        if ($asset->purchase_source_type === 'existing') {
            return null;
        }

        // Determine Asset Account (Debit)
        $assetAccountId = $asset->chart_of_account_id
            ?? self::getAccountIdByMapping('default_asset_account', '1-7100');

        if (!$assetAccountId) {
            return null;
        }

        // Determine Credit Account based on purchase source type
        $creditAccountId = null;
        $creditMemo = '';

        if ($asset->purchase_source_type === 'equity') {
            // Credit: Modal Pemilik (3-1000)
            $equityCoa = ChartOfAccount::where('code', '3-1000')->first();
            $creditAccountId = $equityCoa?->id;
            $creditMemo = "Setoran Modal Pemilik untuk Aset: {$asset->name}";
        } else {
            // Default: 'cash'
            $pm = $asset->paymentMethod;
            $creditAccountId = ($pm && $pm->chart_of_account_id)
                ? $pm->chart_of_account_id
                : self::getAccountIdByMapping('default_cash', '1-1000');
            $creditMemo = "Pengeluaran Kas/Bank untuk Pembelian Aset: {$asset->name}";
        }

        if (!$creditAccountId) {
            return null;
        }

        $items = [
            [
                'chart_of_account_id' => $assetAccountId,
                'debit' => $cost,
                'credit' => 0,
                'memo' => "Perolehan Aset Tetap: {$asset->name} ({$asset->code})",
            ],
            [
                'chart_of_account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $cost,
                'memo' => $creditMemo,
            ],
        ];

        $sourceLabel = ($asset->purchase_source_type === 'equity') ? 'Setoran Modal' : 'Pembelian Kas/Bank';

        $journal = self::createJournalEntry([
            'entry_date' => $asset->purchase_date ? $asset->purchase_date->toDateString() : now()->toDateString(),
            'description' => "Perolehan Aset ({$sourceLabel}): {$asset->name} ({$asset->code})",
            'reference_type' => Asset::class,
            'reference_id' => $asset->id,
            'created_by' => Auth::id(),
            'items' => $items,
        ]);

        $asset->update(['purchase_journal_entry_id' => $journal->id]);

        return $journal;
    }

    /**
     * Record journal entry for monthly Asset Depreciation.
     */
    public static function recordAssetDepreciationJournal(AssetDepreciation $depreciation): ?JournalEntry
    {
        $existing = JournalEntry::where('reference_type', AssetDepreciation::class)
            ->where('reference_id', $depreciation->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $asset = $depreciation->asset;
        if (!$asset) {
            return null;
        }

        $amount = (float) $depreciation->depreciation_amount;
        if ($amount <= 0) {
            return null;
        }

        $expenseAccountId = $asset->depreciation_expense_account_id
            ?? self::getAccountIdByMapping('default_asset_depr_expense', '5-7000');

        $accumulatedAccountId = $asset->accumulated_depreciation_account_id
            ?? self::getAccountIdByMapping('default_asset_accum_depr', '1-7900');

        if (!$expenseAccountId || !$accumulatedAccountId) {
            return null;
        }

        $items = [
            [
                'chart_of_account_id' => $expenseAccountId,
                'debit' => $amount,
                'credit' => 0,
                'memo' => "Beban Penyusutan Asset: {$asset->name} ({$asset->code})",
            ],
            [
                'chart_of_account_id' => $accumulatedAccountId,
                'debit' => 0,
                'credit' => $amount,
                'memo' => "Akumulasi Penyusutan Asset: {$asset->name} ({$asset->code})",
            ],
        ];

        $journal = self::createJournalEntry([
            'entry_date' => $depreciation->period_date ? $depreciation->period_date->toDateString() : now()->toDateString(),
            'description' => "Penyusutan Aset: {$asset->name} ({$asset->code}) Periode " . ($depreciation->period_date ? $depreciation->period_date->format('M Y') : now()->format('M Y')),
            'reference_type' => AssetDepreciation::class,
            'reference_id' => $depreciation->id,
            'created_by' => Auth::id(),
            'items' => $items,
        ]);

        $depreciation->update(['journal_entry_id' => $journal->id]);

        return $journal;
    }
}
