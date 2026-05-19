<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Carbon\Carbon;

class PaymentManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $paymentId;

    // List & Search & Filters
    public $search = '';
    public $filterPaymentMethod = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';

    // Form fields
    public $registration_id, $payment_method_id, $payment_number;
    public $payment_date, $amount, $notes, $status = 'Lunas';
    public $proof_of_payment;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    public function generatePaymentNumber()
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

    public function updatedRegistrationId($value)
    {
        if ($value) {
            $registration = Registration::find($value);
            if ($registration) {
                $this->amount = $registration->total_price;
            }
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->paymentId = $id;
            $payment = Payment::find($id);
            $this->registration_id = $payment->registration_id;
            $this->payment_method_id = $payment->payment_method_id;
            $this->payment_number = $payment->payment_number;
            $this->payment_date = $payment->payment_date->format('Y-m-d');
            $this->amount = $payment->amount;
            $this->notes = $payment->notes;
            $this->status = $payment->status;
        } else {
            $this->generatePaymentNumber();
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
        $this->paymentId = null;
        $this->registration_id = null;
        $this->payment_method_id = null;
        $this->payment_number = null;
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->amount = null;
        $this->notes = null;
        $this->status = 'Lunas';
        $this->proof_of_payment = null;
        $this->generatePaymentNumber();
    }

    public function savePayment()
    {
        $rules = [
            'registration_id' => 'required|exists:registrations,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'proof_of_payment' => 'nullable|image|max:2048',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $data = [
                'registration_id' => $this->registration_id,
                'payment_method_id' => $this->payment_method_id,
                'payment_number' => $this->payment_number,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'notes' => $this->notes,
                'status' => $this->status,
            ];

            if ($this->paymentId) {
                $payment = Payment::find($this->paymentId);
                if ($this->proof_of_payment) {
                    if ($payment->proof_of_payment) Storage::disk('public')->delete($payment->proof_of_payment);
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                $payment->update($data);
            } else {
                if ($this->proof_of_payment) {
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                Payment::create($data);
            }
        });

        $message = "Pembayaran berhasil disimpan.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deletePayment($id)
    {
        $payment = Payment::find($id);
        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }
        $payment->delete();

        DatabaseUpdated::dispatch();
        $message = "Pembayaran berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterPaymentMethod() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterPaymentMethod', 'filterDateStart', 'filterDateEnd']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Payment::with('registration.user', 'paymentMethod');

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('registration.user', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('payment_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterPaymentMethod) {
            $query->where('payment_method_id', $this->filterPaymentMethod);
        }

        if ($this->filterDateStart) {
            $query->whereDate('payment_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->whereDate('payment_date', '<=', $this->filterDateEnd);
        }

        return view('livewire.payment-manager', [
            'payments' => $query->latest()->paginate(10),
            'registrations' => Registration::with('user', 'room')->where('status', 'active')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
        ]);
    }
}
