<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Rule;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Registration $registration)
    {
        $registration->load(['user', 'location', 'room', 'emergencyContacts']);

        $rules = Rule::where('is_active', true)
            ->where(function ($query) use ($registration) {
                $query->whereNull('location_id')
                    ->orWhere('location_id', $registration->location_id);
            })
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        return view('invoices.print', compact('registration', 'rules'));
    }

    public function billInvoice(Bill $bill)
    {
        $bill->load(['registration.user', 'registration.location', 'registration.room']);
        return view('invoices.bill', compact('bill'));
    }

    public function paymentInvoice(Payment $payment)
    {
        $payment->load([
            'registration.user',
            'registration.location',
            'registration.room',
            'bill.payments.paymentMethod',
            'paymentMethod'
        ]);

        $totalPaid = 0;
        $remaining = 0;

        if (!$payment->bill) {
            $totalPaid = Payment::where('registration_id', $payment->registration_id)
                ->where('status', '!=', 'Menunggu Konfirmasi')
                ->sum('amount');
            $remaining = max(0, $payment->registration->total_price - $totalPaid);
        }

        return view('invoices.payment', compact('payment', 'totalPaid', 'remaining'));
    }
}
