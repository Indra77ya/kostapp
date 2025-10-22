<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class JournalService
{
    public function postInvoiceAccrual($invoice)
    {
        $accAR  = Account::where('code','1120')->firstOrFail(); // Piutang
        $accRev = Account::where('code','4100')->firstOrFail(); // Pendapatan Sewa

        $je = JournalEntry::create([
            'date' => now()->toDateString(),
            'ref'  => $invoice->number,
            'memo' => 'Akrual pendapatan sewa',
        ]);

        JournalLine::create([
            'journal_entry_id'=>$je->id, 'account_id'=>$accAR->id, 'debit'=>$invoice->total,'credit'=>0
        ]);
        JournalLine::create([
            'journal_entry_id'=>$je->id, 'account_id'=>$accRev->id, 'debit'=>0,'credit'=>$invoice->total
        ]);

        return $je;
    }

    public function postPayment($payment)
    {
        $accCash = Account::where('code','1110')->firstOrFail(); // Bank
        $accAR   = Account::where('code','1120')->firstOrFail(); // Piutang

        $je = JournalEntry::create([
            'date' => now()->toDateString(),
            'ref'  => $payment->reference,
            'memo' => 'Pembayaran invoice '.$payment->invoice->number,
        ]);

        JournalLine::create([
            'journal_entry_id'=>$je->id, 'account_id'=>$accCash->id, 'debit'=>$payment->amount,'credit'=>0
        ]);
        JournalLine::create([
            'journal_entry_id'=>$je->id, 'account_id'=>$accAR->id, 'debit'=>0,'credit'=>$payment->amount
        ]);

        return $je;
    }
}
