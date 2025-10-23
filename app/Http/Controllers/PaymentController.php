<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayInvoiceRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\JournalService;

class PaymentController extends Controller
{
    public function store(Invoice $invoice, PayInvoiceRequest $request, JournalService $journal)
    {
        $data = $request->validated();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'paid_at'    => now()->toDateString(),
            'amount'     => $data['amount'],
            'method'     => $data['method'],
            'reference'  => $data['reference'] ?: 'PAY-'.strtoupper(str()->random(6)),
        ]);

        // update status invoice
        $paid = $invoice->payments()->sum('amount');
        if ($paid >= $invoice->total) {
            $invoice->update(['status'=>'paid']);
        } elseif ($paid > 0) {
            $invoice->update(['status'=>'partial']);
        }

        // jurnal
        $journal->postPayment($payment);

        return back()->with('ok','Pembayaran tercatat.');
    }
}
