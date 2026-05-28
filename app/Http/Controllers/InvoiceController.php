<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Rule;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private function authorizeTenant($registrationId)
    {
        $user = auth()->user();
        if ($user->hasRole('tenant')) {
            $registration = Registration::find($registrationId);
            if (!$registration || $registration->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke kuitansi ini.');
            }
        }
    }

    public function show(Registration $registration)
    {
        $this->authorizeTenant($registration->id);
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
        $this->authorizeTenant($bill->registration_id);
        $bill->load(['registration.user', 'registration.location', 'registration.room']);
        return view('invoices.bill', compact('bill'));
    }

    public function paymentInvoice(Payment $payment)
    {
        $this->authorizeTenant($payment->registration_id);
        $payment->load([
            'registration.user',
            'registration.location',
            'registration.room',
            'bill.payments.paymentMethod',
            'paymentMethod'
        ]);

        return view('invoices.payment', compact('payment'));
    }
}
