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
    public $status = 'Lunas';
    public $excess_amount = 0;
    public $sender_bank_name, $sender_account_number, $sender_account_name;
    public $payment_number;

    public function getListeners()
    {
        $userId = Auth::id();
        return [
            "echo-private:App.Models.User.{$userId},DatabaseUpdated" => '$refresh',
        ];
    }

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

    public function openModal($billId = 'umum')
    {
        $this->resetValidation();
        $this->resetForm();
        $this->bill_id = $billId;

        if (is_numeric($billId)) {
            $bill = Bill::find($billId);
            if ($bill) {
                // Calculate remaining for this specific bill
                $totalPaidOnThisBill = Payment::where('bill_id', $billId)
                    ->where('status', '!=', 'Menunggu Konfirmasi')
                    ->where('status', '!=', 'Ditolak')
                    ->sum('amount');

                // Also subtract pending payments
                $pendingAmount = Payment::where('bill_id', $billId)
                    ->where('status', 'Menunggu Konfirmasi')
                    ->sum('amount');

                $this->amount = $bill->amount - $totalPaidOnThisBill - $pendingAmount;
                if ($this->amount < 0) $this->amount = 0;
            }
        }

        $this->calculateStatus();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->bill_id = 'umum';
        $this->payment_method_id = null;
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->amount = null;
        $this->notes = null;
        $this->status = '';
        $this->excess_amount = 0;
        $this->proof_of_payment = null;
        $this->sender_bank_name = null;
        $this->sender_account_number = null;
        $this->sender_account_name = null;
        $this->generatePaymentNumber();
    }

    public function updatedBillId()
    {
        if (is_numeric($this->bill_id)) {
            $bill = Bill::find($this->bill_id);
            if ($bill) {
                $totalPaidOnThisBill = Payment::where('bill_id', $this->bill_id)
                    ->where('status', '!=', 'Ditolak')
                    ->sum('amount');

                $this->amount = $bill->amount - $totalPaidOnThisBill;
                if ($this->amount < 0) $this->amount = 0;
            }
        }
        $this->calculateStatus();
    }

    public function updatedAmount()
    {
        $this->calculateStatus();
    }

    private function calculateStatus()
    {
        $this->excess_amount = 0;
        if ($this->bill_id === 'umum') {
            $this->status = "Pembayaran Umum";
        } elseif ($this->bill_id === 'deposit') {
            $this->status = "Setor Deposit";
        } elseif (is_numeric($this->bill_id)) {
            $bill = Bill::find($this->bill_id);
            if (!$bill) return;

            $totalPaidPrev = Payment::where('bill_id', $this->bill_id)
                ->where('status', '!=', 'Ditolak')
                ->sum('amount');

            $currentAmount = (float) ($this->amount ?: 0);
            $totalPaidNow = $totalPaidPrev + $currentAmount;
            $totalBill = (float) $bill->amount;

            $diff = $totalBill - $totalPaidNow;

            if ($diff > 0) {
                $formattedDiff = number_format($diff, 0, ',', '.');
                $this->status = "Belum Lunas (Sisa: Rp {$formattedDiff})";
            } elseif ($diff < 0) {
                $this->excess_amount = abs($diff);
                $formattedDiff = number_format($this->excess_amount, 0, ',', '.');
                $this->status = "Lunas (Deposit: Rp {$formattedDiff})";
            } else {
                $this->status = "Lunas";
            }
        } else {
            $this->status = "";
        }
    }

    public function savePayment()
    {
        $pm = PaymentMethod::find($this->payment_method_id);
        $isTunai = $pm && $pm->category === 'Tunai';

        $rules = [
            'bill_id' => 'nullable',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'proof_of_payment' => $isTunai ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ];

        if (!$isTunai) {
            $rules['sender_bank_name'] = 'required';
            $rules['sender_account_number'] = 'required';
            $rules['sender_account_name'] = 'required';
        }

        $this->validate($rules);

        $registration = Registration::where('user_id', Auth::id())->where('status', 'active')->first();

        if (!$registration) {
            $this->dispatch('notify', message: 'Anda tidak memiliki registrasi aktif.', type: 'danger');
            return;
        }

        $actualBillId = is_numeric($this->bill_id) ? $this->bill_id : null;
        $isDeposit = $this->bill_id === 'deposit';

        $data = [
            'registration_id' => $registration->id,
            'bill_id' => $actualBillId,
            'payment_method_id' => $this->payment_method_id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'sender_bank_name' => $this->sender_bank_name,
            'sender_account_number' => $this->sender_account_number,
            'sender_account_name' => $this->sender_account_name,
            'notes' => ($isDeposit ? '[DEPOSIT] ' : '') . $this->notes,
            'status' => 'Menunggu Konfirmasi',
        ];

        if ($this->proof_of_payment) {
            $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
        }

        Payment::create($data);

        $this->dispatch('notify', message: 'Pembayaran berhasil dikirim. Menunggu konfirmasi admin.', type: 'success');
        broadcast(new NotificationSent('Pembayaran baru dikirim oleh penghuni.', 'info'))->toOthers();
        DatabaseUpdated::dispatch(Auth::id());

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

        $selectedPm = $this->payment_method_id ? PaymentMethod::find($this->payment_method_id) : null;

        return view('livewire.tenant-payment-manager', [
            'registration' => $registration,
            'bills' => $bills,
            'payments' => $payments,
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
            'selectedPm' => $selectedPm,
        ]);
    }
}
