<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\Registration;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class TenantPaymentManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $viewMode = 'overview'; // 'overview' or 'history'
    public $isModalOpen = false;

    // Form fields
    public $bill_id, $payment_method_id, $payment_date, $amount, $notes, $proof_of_payment;
    public $payment_number;

    public function mount()
    {
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    private function generatePaymentNumber()
    {
        $date = Carbon::now()->format('dmY');
        $prefix = "PAY-{$date}-";

        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $this->payment_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function openModal($billId = null)
    {
        $this->resetValidation();
        $this->resetForm();
        $this->bill_id = $billId;

        if ($billId) {
            $bill = Bill::find($billId);
            if ($bill) {
                // Calculate remaining for this specific bill
                $totalPaidOnThisBill = Payment::where('bill_id', $billId)
                    ->where('status', '!=', 'Menunggu Konfirmasi')
                    ->sum('amount');

                // Also subtract pending payments
                $pendingAmount = Payment::where('bill_id', $billId)
                    ->where('status', 'Menunggu Konfirmasi')
                    ->sum('amount');

                $this->amount = $bill->amount - $totalPaidOnThisBill - $pendingAmount;
                if ($this->amount < 0) $this->amount = 0;
            }
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->bill_id = null;
        $this->payment_method_id = null;
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->amount = null;
        $this->notes = null;
        $this->proof_of_payment = null;
        $this->generatePaymentNumber();
    }

    public function savePayment()
    {
        $this->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'proof_of_payment' => 'required|image|max:2048',
        ]);

        $registration = Registration::where('user_id', Auth::id())->where('status', 'active')->first();

        if (!$registration) {
            $this->dispatch('notify', message: 'Anda tidak memiliki registrasi aktif.', type: 'danger');
            return;
        }

        $data = [
            'registration_id' => $registration->id,
            'bill_id' => $this->bill_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => 'Menunggu Konfirmasi',
        ];

        if ($this->proof_of_payment) {
            $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
        }

        Payment::create($data);

        $this->dispatch('notify', message: 'Pembayaran berhasil dikirim. Menunggu konfirmasi admin.', type: 'success');
        broadcast(new NotificationSent('Pembayaran baru dikirim oleh penghuni.', 'info'))->toOthers();
        DatabaseUpdated::dispatch();

        $this->closeModal();
    }

    public function render()
    {
        $registration = Registration::with('room', 'location')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        $bills = [];
        $payments = [];

        if ($registration) {
            $bills = Bill::where('registration_id', $registration->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $payments = Payment::with(['paymentMethod', 'bill'])
                ->where('registration_id', $registration->id)
                ->latest()
                ->paginate(10);
        }

        return view('livewire.tenant-payment-manager', [
            'registration' => $registration,
            'bills' => $bills,
            'payments' => $payments,
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
        ]);
    }
}
