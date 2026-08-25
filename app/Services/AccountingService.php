<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Expense;
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

        $cashAccount = self::getAccountByCode('1-1000');
        $depositLiabilityAccount = self::getAccountByCode('2-1000');
        $rentalRevenueAccount = self::getAccountByCode('4-1000');

        if (!$cashAccount || !$depositLiabilityAccount || !$rentalRevenueAccount) {
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
                'chart_of_account_id' => $depositLiabilityAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Penggunaan Saldo Deposit untuk Tagihan',
            ];
            $items[] = [
                'chart_of_account_id' => $rentalRevenueAccount->id,
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
                'chart_of_account_id' => $depositLiabilityAccount->id,
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
                'chart_of_account_id' => $rentalRevenueAccount->id,
                'debit' => 0,
                'credit' => $revenueAmount,
                'memo' => 'Pendapatan Sewa Kamar',
            ];
        }

        if ($excessAmount > 0) {
            $items[] = [
                'chart_of_account_id' => $depositLiabilityAccount->id,
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

        $cashAccount = self::getAccountByCode('1-1000');
        if (!$cashAccount) {
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
                'chart_of_account_id' => $cashAccount->id,
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

        $depositLiabilityAccount = self::getAccountByCode('2-1000');
        $claimRevenueAccount = self::getAccountByCode('4-3000');
        $cashAccount = self::getAccountByCode('1-1000');

        if (!$depositLiabilityAccount) {
            return null;
        }

        $items = [];
        $totalDepositCleared = $deductionAmount + $refundAmount;

        // Debit: Utang Deposit Tenant (reduce total liability)
        $items[] = [
            'chart_of_account_id' => $depositLiabilityAccount->id,
            'debit' => $totalDepositCleared,
            'credit' => 0,
            'memo' => 'Penyelesaian Deposit Check Out ' . optional($registration->user)->name,
        ];

        // Credit: Claim revenue for deduction
        if ($deductionAmount > 0 && $claimRevenueAccount) {
            $items[] = [
                'chart_of_account_id' => $claimRevenueAccount->id,
                'debit' => 0,
                'credit' => $deductionAmount,
                'memo' => 'Pendapatan Potongan Kerusakan Deposit',
            ];
        }

        // Credit: Cash for refund
        if ($refundAmount > 0 && $cashAccount) {
            $items[] = [
                'chart_of_account_id' => $cashAccount->id,
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
}
